<?php

namespace App\Livewire\Candidature;

use App\Enums\CandidatureStatut;
use App\Enums\DocumentStatutValidation;
use App\Mail\CandidatureSoumiseMail;
use App\Models\Candidat;
use App\Models\Candidature;
use App\Models\CandidatureDocument;
use App\Models\Programme;
use App\Models\ProgrammeNiveau;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Livewire\Component;
use Livewire\TemporaryUploadedFile;
use Livewire\WithFileUploads;
use Throwable;

class Formulaire extends Component
{
    use WithFileUploads;

    public int $step = 1;

    // ===== Étape 1 : Informations personnelles =====
    public string $nom = '';

    public string $prenom = '';

    public string $telephone = '';

    public string $date_naissance = '';

    public string $email = '';

    public string $lieu_naissance = '';

    public string $nationalite = '';

    public string $sexe = '';

    // ===== Étape 2 : Informations académiques =====
    public string $pays = '';

    public string $adresse = '';

    public string $dernier_diplome = '';

    public string $serie_baccalaureat = '';

    // ===== Étape 3 : Programme choisi =====
    public ?int $programme_id = null;

    public ?int $programme_niveau_id = null;

    // ===== Étape 4 : Documents dynamiques =====
    /**
     * Uploaded documents by code (wire:model)
     *
     * @var array<string, TemporaryUploadedFile|UploadedFile>
     */
    public array $documents = [];

    /**
     * @var array<int, array<string, mixed>>
     */
    public array $programmeCatalog = [];

    public bool $submitted = false;

    public bool $emailSent = false;

    protected array $serieProgrammeNiveaux = [
        'S' => ['classe_preparatoire', 'licence', 'cycle_ingenieur', 'master'],
        'L' => ['licence', 'master'],
        'G' => ['licence', 'master'],
    ];

    protected array $programmeCategoryLabels = [
        'classe_preparatoire' => 'CPGE',
        'licence' => 'Licences',
        'cycle_ingenieur' => 'Cycle Ingénieur',
        'master' => 'Masters',
    ];

    public function mount(): void
    {
        $this->programmeCatalog = Programme::query()
            ->where('actif', true)
            ->whereHas('niveaux', fn ($query) => $query->where('actif', true))
            ->with([
                'niveaux' => fn ($query) => $query
                    ->where('actif', true)
                    ->with('niveau')
                    ->orderBy('ordre'),
            ])
            ->orderBy('nom')
            ->get()
            ->mapWithKeys(function (Programme $programme): array {
                return [
                    $programme->id => [
                        'id' => $programme->id,
                        'nom' => $programme->nom,
                        'slug' => $programme->slug,
                        'niveau' => $programme->niveau,
                        'description' => $programme->description,
                        'niveaux' => $programme->niveaux
                            ->map(fn (ProgrammeNiveau $programmeNiveau): array => [
                                'id' => $programmeNiveau->id,
                                'code' => $programmeNiveau->niveau->code,
                                'libelle' => $programmeNiveau->niveau->libelle,
                            ])
                            ->values()
                            ->all(),
                    ],
                ];
            })
            ->all();
    }

    public function updatedSerieBaccalaureat(): void
    {
        if (! $this->programmeBelongsToSelectedSerie()) {
            $this->programme_id = null;
            $this->programme_niveau_id = null;
            $this->documents = [];
        }
    }

    public function updatedProgrammeId(): void
    {
        $this->programme_niveau_id = null;
        $this->documents = [];
    }

    public function updatedProgrammeNiveauId(): void
    {
        $this->documents = [];
    }

    public function updatedEmail(string $value): void
    {
        $this->email = mb_strtolower(trim($value));
    }

    public function updated($property): void
    {
        try {
            $this->validateOnly($property, $this->rules(), $this->messages(), $this->validationAttributes());
        } catch (ValidationException) {
            // géré par Livewire
        }
    }

    public function previousStep(): void
    {
        $this->step = max(1, $this->step - 1);
    }

    public function nextStep(): void
    {
        $this->validateCurrentStep();
        $this->step = min(count($this->stepsMeta()), $this->step + 1);
    }

    public function save(): void
    {
        $this->step = count($this->stepsMeta());
        $validated = $this->validate(
            array_merge($this->rules(), $this->documentsValidationRules()),
            $this->messages(),
            $this->validationAttributes(),
        );

        $programmeData = $this->selectedProgrammeData();
        $programmeNiveauData = $this->resolvedProgrammeNiveauData();

        if (! $programmeData) {
            $this->addError('programme_id', 'La formation sélectionnée est introuvable.');

            return;
        }

        if (! $programmeNiveauData) {
            $this->addError('programme_niveau_id', 'Le niveau sélectionné est introuvable pour cette formation.');

            return;
        }

        $candidatureExistante = Candidature::query()
            ->where('programme_id', $programmeData['id'])
            ->whereHas(
                'candidat',
                fn ($query) => $query->where('email', mb_strtolower($validated['email'])),
            )
            ->exists();

        if ($candidatureExistante) {
            $this->addError(
                'programme_id',
                'Une candidature existe déjà pour cette adresse e-mail et cette formation.',
            );

            return;
        }

        $candidature = DB::transaction(function () use ($validated, $programmeData, $programmeNiveauData): Candidature {
            $candidat = Candidat::firstOrNew(['email' => mb_strtolower($validated['email'])]);
            $candidat->fill([
                'nom' => $validated['nom'],
                'prenom' => $validated['prenom'],
                'telephone' => $validated['telephone'],
                'date_naissance' => $validated['date_naissance'],
                'email' => mb_strtolower($validated['email']),
                'pays' => $validated['pays'],
                'adresse' => $validated['adresse'],
                'lieu_naissance' => $validated['lieu_naissance'] ?? null,
                'nationalite' => $validated['nationalite'] ?? null,
                'sexe' => $validated['sexe'] ?? null,
            ]);
            $candidat->save();

            $candidature = Candidature::make([
                'candidat_id' => $candidat->id,
                'programme_id' => $programmeData['id'],
            ]);
            $candidature->edit_token = (string) Str::uuid();
            $candidature->fill([
                'programme_niveau_id' => $programmeNiveauData['id'],
                'statut' => CandidatureStatut::SOUMISE,
                'etape_courante' => count($this->stepsMeta()),
                'derniere_formation' => $validated['dernier_diplome'],
                'submitted_at' => now(),
                'locked_identity_at' => now(),
            ]);
            $candidature->save();

            foreach ($this->documents as $code => $file) {
                if (! $file) {
                    continue;
                }

                $originalName = $file->getClientOriginalName();
                $mimeType = $file->getClientMimeType();
                $size = $file->getSize();
                $path = $file->store('candidature_documents', 'local');
                $type = DB::table('types_documents')->where('code', $code)->first();
                if (! $type) {
                    continue;
                }
                CandidatureDocument::create([
                    'candidature_id' => $candidature->id,
                    'type_document_id' => $type->id,
                    'original_name' => $originalName,
                    'stored_name' => basename($path),
                    'path' => $path,
                    'mime_type' => $mimeType,
                    'size' => $size,
                    'statut_validation' => DocumentStatutValidation::EN_ATTENTE,
                ]);
            }

            return $candidature;
        });

        $this->submitted = true;

        try {
            Mail::to($candidature->candidat->email)
                ->send(new CandidatureSoumiseMail($candidature));
            $this->emailSent = true;
        } catch (Throwable $exception) {
            report($exception);
        }
    }

    public function startNewApplication(): void
    {
        $this->reset([
            'step', 'nom', 'prenom', 'telephone', 'date_naissance', 'email',
            'lieu_naissance', 'nationalite', 'sexe',
            'pays', 'adresse', 'dernier_diplome', 'serie_baccalaureat',
            'programme_id', 'programme_niveau_id', 'documents', 'submitted', 'emailSent',
        ]);
        $this->step = 1;
        $this->resetValidation();
    }

    public function render()
    {
        return view('livewire.candidature.formulaire', [
            'stepsMeta' => $this->stepsMeta(),
            'availableProgrammes' => $this->availableProgrammes(),
            'selectedProgramme' => $this->selectedProgrammeData(),
            'selectedProgrammeNiveau' => $this->resolvedProgrammeNiveauData(),
            'progressPercent' => (int) round(($this->step / count($this->stepsMeta())) * 100),
            'requiredDocuments' => $this->requiredDocuments(),
            'canSubmit' => $this->canSubmit(),
        ]);
    }

    protected function validateCurrentStep(): void
    {
        $rules = match ($this->step) {
            1 => Arr::only($this->rules(), ['nom', 'prenom', 'telephone', 'date_naissance', 'email', 'lieu_naissance', 'nationalite', 'sexe']),
            2 => Arr::only($this->rules(), ['pays', 'adresse', 'dernier_diplome', 'serie_baccalaureat']),
            3 => Arr::only($this->rules(), ['programme_id', 'programme_niveau_id']),
            4 => $this->documentsValidationRules(),
            default => [],
        };
        $this->validate($rules, $this->messages(), $this->validationAttributes());
    }

    public function rules(): array
    {
        return [
            'nom' => ['required', 'string', 'max:100'],
            'prenom' => ['required', 'string', 'max:100'],
            'telephone' => ['required', 'string', 'max:30'],
            'date_naissance' => ['required', 'date', 'before_or_equal:today'],
            'email' => ['required', 'email:rfc,dns', 'max:255'],
            'lieu_naissance' => ['required', 'string', 'max:120'],
            'nationalite' => ['required', 'string', 'max:80'],
            'sexe' => ['required', 'in:masculin,feminin'],
            'pays' => ['required', 'string', 'max:100'],
            'adresse' => ['required', 'string', 'max:500'],
            'dernier_diplome' => ['required', 'in:baccalaureat,licence'],
            'serie_baccalaureat' => ['nullable', 'in:S,L,G'],
            'programme_id' => [
                'required',
                'integer',
                Rule::exists('programmes', 'id')->where('actif', true),
            ],
            'programme_niveau_id' => [
                'required',
                'integer',
                Rule::exists('programme_niveaux', 'id')->where(
                    fn ($query) => $query
                        ->where('programme_id', $this->programme_id)
                        ->where('actif', true),
                ),
            ],
        ];
    }

    protected function messages(): array
    {
        return [
            'required' => 'Ce champ est obligatoire.',
            'email' => 'Veuillez saisir une adresse e-mail valide.',
            'date_naissance.before_or_equal' => 'La date de naissance doit être antérieure ou égale à aujourd\'hui.',
            'in' => 'La valeur choisie n\'est pas valide.',
        ];
    }

    protected function validationAttributes(): array
    {
        return [
            'nom' => 'nom',
            'prenom' => 'prénom',
            'telephone' => 'numéro de téléphone',
            'date_naissance' => 'date de naissance',
            'email' => 'adresse e-mail',
            'lieu_naissance' => 'lieu de naissance',
            'nationalite' => 'nationalité',
            'sexe' => 'sexe',
            'pays' => 'pays',
            'adresse' => 'adresse',
            'dernier_diplome' => 'dernier diplôme obtenu',
            'serie_baccalaureat' => 'série du Baccalauréat',
            'programme_id' => 'formation visée',
            'programme_niveau_id' => 'niveau demandé',
        ];
    }

    /**
     * 4 étapes de saisie + 1 récapitulatif = 5 étapes au total.
     */
    protected function stepsMeta(): array
    {
        return [
            ['number' => 1, 'label' => 'Informations personnelles'],
            ['number' => 2, 'label' => 'Informations académiques'],
            ['number' => 3, 'label' => 'Programme choisi'],
            ['number' => 4, 'label' => 'Documents à joindre'],
        ];
    }

    protected function availableProgrammes(): array
    {
        if ($this->serie_baccalaureat === '' || ! isset($this->serieProgrammeNiveaux[$this->serie_baccalaureat])) {
            return [];
        }
        $allowedNiveaux = $this->serieProgrammeNiveaux[$this->serie_baccalaureat];
        $groups = [];
        foreach ($allowedNiveaux as $niveau) {
            $options = collect($this->programmeCatalog)
                ->filter(fn (array $programme) => $programme['niveau'] === $niveau)
                ->values()
                ->all();
            if (count($options) > 0) {
                $groups[] = [
                    'label' => $this->programmeCategoryLabels[$niveau] ?? Str::headline($niveau),
                    'options' => $options,
                ];
            }
        }

        return $groups;
    }

    /**
     * Documents requis = dépend de la formation visée et de son niveau.
     * Source : database/seeders/ProgrammesSeeder.php (table programme_niveau_type_document).
     *
     * @return array<int, array<string,mixed>>
     */
    protected function requiredDocuments(): array
    {
        $niveau = $this->resolvedProgrammeNiveauData();

        if (! $niveau || ! isset($niveau['id'])) {
            return [];
        }

        $rows = DB::table('programme_niveau_type_document as p')
            ->join('types_documents as t', 'p.type_document_id', '=', 't.id')
            ->where('p.programme_niveau_id', $niveau['id'])
            ->orderBy('p.ordre')
            ->get(['t.id', 't.code', 't.libelle', 't.extensions_autorisees', 't.taille_max_mb', 'p.obligatoire']);

        return collect($rows)->map(function ($r) {
            return [
                'id' => $r->id,
                'code' => $r->code,
                'libelle' => $r->libelle,
                'extensions_autorisees' => is_string($r->extensions_autorisees) ? json_decode($r->extensions_autorisees, true) ?? [] : ($r->extensions_autorisees ?? []),
                'taille_max_mb' => $r->taille_max_mb,
                'obligatoire' => (bool) $r->obligatoire,
            ];
        })->all();
    }

    protected function documentsValidationRules(): array
    {
        $rules = [];
        foreach ($this->requiredDocuments() as $doc) {
            $key = 'documents.'.$doc['code'];
            $rules[$key] = $doc['obligatoire'] ? ['required', 'file'] : ['nullable', 'file'];
            if (! empty($doc['taille_max_mb'])) {
                $rules[$key][] = 'max:'.($doc['taille_max_mb'] * 1024);
            }
            if (! empty($doc['extensions_autorisees'])) {
                $mimes = array_map(fn ($ext) => ltrim($ext, '.'), $doc['extensions_autorisees']);
                $rules[$key][] = 'mimes:'.implode(',', $mimes);
            }
        }

        return $rules;
    }

    protected function canSubmit(): bool
    {
        $champsComplets = ! blank($this->nom)
            && ! blank($this->prenom)
            && ! blank($this->telephone)
            && ! blank($this->date_naissance)
            && ! blank($this->email)
            && ! blank($this->lieu_naissance)
            && ! blank($this->nationalite)
            && ! blank($this->sexe)
            && ! blank($this->pays)
            && ! blank($this->adresse)
            && ! blank($this->dernier_diplome)
            && ! blank($this->programme_id)
            && ! blank($this->programme_niveau_id);

        if (! $champsComplets) {
            return false;
        }

        return collect($this->requiredDocuments())
            ->where('obligatoire', true)
            ->every(fn (array $document): bool => ! empty($this->documents[$document['code']]));
    }

    protected function selectedProgrammeData(): ?array
    {
        if (! $this->programme_id || ! isset($this->programmeCatalog[$this->programme_id])) {
            return null;
        }

        return $this->programmeCatalog[$this->programme_id];
    }

    protected function resolvedProgrammeNiveauData(): ?array
    {
        $programme = $this->selectedProgrammeData();
        if (! $programme) {
            return null;
        }
        if (! $this->programme_niveau_id) {
            return null;
        }

        return collect($programme['niveaux'])
            ->firstWhere('id', $this->programme_niveau_id);
    }

    protected function programmeBelongsToSelectedSerie(): bool
    {
        if (! $this->programme_id || $this->serie_baccalaureat === '') {
            return false;
        }
        $allowedIds = collect($this->availableProgrammes())
            ->flatMap(fn (array $group) => collect($group['options'])->pluck('id'))
            ->all();

        return in_array($this->programme_id, $allowedIds, true);
    }
}
