<?php

namespace App\Http\Controllers\BackOffice\Administration;

use App\Http\Controllers\Controller;
use App\Models\TypeDocument;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class TypeDocumentController extends Controller
{
    /**
     * @var array<string, string>
     */
    private const EXTENSIONS = [
        'pdf' => 'PDF',
        'jpg' => 'JPG',
        'jpeg' => 'JPEG',
        'png' => 'PNG',
    ];

    public function index(Request $request): View
    {
        $filtres = $request->validate([
            'recherche' => ['nullable', 'string', 'max:255'],
            'etat' => ['nullable', Rule::in(['actif', 'inactif'])],
        ]);

        $typesDocuments = TypeDocument::query()
            ->withCount('niveauxProgrammes')
            ->when($filtres['recherche'] ?? null, function (Builder $query, string $recherche): void {
                $query->where(function (Builder $query) use ($recherche): void {
                    $query
                        ->where('libelle', 'like', "%{$recherche}%")
                        ->orWhere('code', 'like', "%{$recherche}%");
                });
            })
            ->when(
                $filtres['etat'] ?? null,
                fn (Builder $query, string $etat) => $query->where('actif', $etat === 'actif'),
            )
            ->orderBy('libelle')
            ->paginate(10)
            ->withQueryString();

        return view('back-office.administration.documents.index', [
            'typesDocuments' => $typesDocuments,
            'filtres' => $filtres,
        ]);
    }

    public function create(): View
    {
        return view('back-office.administration.documents.create', [
            'typeDocument' => new TypeDocument,
            'extensions' => self::EXTENSIONS,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $donnees = $this->validerTypeDocument($request);
        $donnees['code'] = $this->genererCodeUnique($donnees['libelle']);
        $donnees['actif'] = false;

        $typeDocument = DB::transaction(function () use ($request, $donnees): TypeDocument {
            $typeDocument = TypeDocument::query()->create($donnees);

            $this->historiser(
                $request->user(),
                'type_document_cree',
                $typeDocument,
                null,
                $this->valeursHistorisees($typeDocument),
            );

            return $typeDocument;
        });

        return redirect()
            ->route('administration.documents.edit', $typeDocument)
            ->with('status', 'Le type de document a été créé inactif. Activez-le lorsqu’il est prêt à être demandé.');
    }

    public function edit(TypeDocument $typeDocument): View
    {
        return view('back-office.administration.documents.edit', [
            'typeDocument' => $typeDocument,
            'extensions' => self::EXTENSIONS,
        ]);
    }

    public function update(Request $request, TypeDocument $typeDocument): RedirectResponse
    {
        $donnees = $this->validerTypeDocument($request);
        $anciennesValeurs = $this->valeursHistorisees($typeDocument);

        DB::transaction(function () use ($request, $typeDocument, $donnees, $anciennesValeurs): void {
            $typeDocument->update($donnees);

            $this->historiser(
                $request->user(),
                'type_document_modifie',
                $typeDocument,
                $anciennesValeurs,
                $this->valeursHistorisees($typeDocument),
            );
        });

        return back()->with('status', 'Le type de document a été modifié. Son code reste inchangé.');
    }

    public function modifierEtat(Request $request, TypeDocument $typeDocument): RedirectResponse
    {
        $donnees = $request->validate([
            'actif' => ['required', 'boolean'],
        ]);
        $nouvelEtat = (bool) $donnees['actif'];

        if ($typeDocument->actif === $nouvelEtat) {
            return back()->with('status', 'Le type de document possède déjà cet état.');
        }

        $anciennesValeurs = $this->valeursHistorisees($typeDocument);

        DB::transaction(function () use ($request, $typeDocument, $nouvelEtat, $anciennesValeurs): void {
            $typeDocument->update(['actif' => $nouvelEtat]);

            $this->historiser(
                $request->user(),
                $nouvelEtat ? 'type_document_active' : 'type_document_desactive',
                $typeDocument,
                $anciennesValeurs,
                $this->valeursHistorisees($typeDocument),
            );
        });

        return back()->with(
            'status',
            $nouvelEtat
                ? 'Le type de document a été activé.'
                : 'Le type de document a été désactivé. Ses anciennes associations sont conservées.',
        );
    }

    /**
     * @return array<string, mixed>
     */
    private function validerTypeDocument(Request $request): array
    {
        return $request->validate([
            'libelle' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:2000'],
            'extensions_autorisees' => ['required', 'array', 'min:1'],
            'extensions_autorisees.*' => [
                'required',
                'string',
                'distinct',
                Rule::in(array_keys(self::EXTENSIONS)),
            ],
            'taille_max_mb' => ['required', 'integer', 'min:1', 'max:50'],
        ]);
    }

    private function genererCodeUnique(string $libelle): string
    {
        $base = Str::slug($libelle, '_') ?: 'document';
        $code = $base;
        $suffixe = 2;

        while (TypeDocument::query()->where('code', $code)->exists()) {
            $code = "{$base}_{$suffixe}";
            $suffixe++;
        }

        return $code;
    }

    /**
     * @return array<string, mixed>
     */
    private function valeursHistorisees(TypeDocument $typeDocument): array
    {
        return [
            'code' => $typeDocument->code,
            'libelle' => $typeDocument->libelle,
            'description' => $typeDocument->description,
            'extensions_autorisees' => $typeDocument->extensions_autorisees,
            'taille_max_mb' => $typeDocument->taille_max_mb,
            'actif' => $typeDocument->actif,
        ];
    }

    /**
     * @param  array<string, mixed>|null  $anciennesValeurs
     * @param  array<string, mixed>|null  $nouvellesValeurs
     */
    private function historiser(
        User $auteur,
        string $action,
        TypeDocument $typeDocument,
        ?array $anciennesValeurs,
        ?array $nouvellesValeurs,
    ): void {
        DB::table('actions_administratives')->insert([
            'auteur_id' => $auteur->id,
            'utilisateur_cible_id' => null,
            'cible_type' => 'type_document',
            'cible_id' => $typeDocument->id,
            'action' => $action,
            'anciennes_valeurs' => $anciennesValeurs === null
                ? null
                : json_encode($anciennesValeurs, JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE),
            'nouvelles_valeurs' => $nouvellesValeurs === null
                ? null
                : json_encode($nouvellesValeurs, JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE),
            'created_at' => now(),
        ]);
    }
}
