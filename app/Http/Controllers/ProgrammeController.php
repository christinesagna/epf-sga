<?php

namespace App\Http\Controllers;

use App\Models\Programme;
use Illuminate\View\View;

class ProgrammeController extends Controller
{
    private array $categories = [
        'cpge' => [
            'niveau' => 'classe_preparatoire',
            'titre' => 'Classes préparatoires aux grandes écoles (CPGE)',
            'description' => 'Formation scientifique destinée aux étudiants souhaitant poursuivre vers le diplôme d’ingénieur.',
        ],
        'licences' => [
            'niveau' => 'licence',
            'titre' => 'Licences (Bac+3)',
            'description' => 'Licences professionnalisantes combinant cours théoriques, travaux pratiques, projets et stages en entreprise.',
        ],
        'cycle-ingenieur' => [
            'niveau' => 'cycle_ingenieur',
            'titre' => 'Cycle ingénieur (Bac+5)',
            'description' => 'Formation d’ingénieur accessible après un niveau Bac+2.',
        ],
        'masters' => [
            'niveau' => 'master',
            'titre' => 'Masters (Bac+5)',
            'description' => 'Formations de spécialisation accessibles après une Licence ou un diplôme équivalent.',
        ],
    ];

    public function index(): View
    {
        $nombresParNiveau = Programme::query()
            ->where('actif', true)
            ->selectRaw('niveau, COUNT(*) as total')
            ->groupBy('niveau')
            ->pluck('total', 'niveau');

        $categories = collect($this->categories)
            ->map(function (array $categorie) use ($nombresParNiveau): array {
                $categorie['nombre_programmes'] = (int) ($nombresParNiveau[$categorie['niveau']] ?? 0);

                return $categorie;
            })
            ->all();

        return view('programmes.index', compact('categories'));
    }

    public function show(string $niveau): View
    {
        abort_unless(isset($this->categories[$niveau]), 404);

        $category = $this->categories[$niveau];
        $programmes = Programme::query()
            ->where('actif', true)
            ->where('niveau', $category['niveau'])
            ->with([
                'niveaux' => fn ($query) => $query
                    ->where('actif', true)
                    ->with('niveau')
                    ->orderBy('ordre'),
            ])
            ->orderBy('nom')
            ->get();

        return view('programmes.show', compact('niveau', 'category', 'programmes'));
    }
}
