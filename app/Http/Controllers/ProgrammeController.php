<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class ProgrammeController extends Controller
{
    private array $categories = [
        'cpge' => [
            'titre' => 'Classes Préparatoires aux Grandes Écoles (CPGE)',
            'description' => 'Formation scientifique destinée aux étudiants souhaitant poursuivre vers le diplôme d\'ingénieur.',
            'contenu' => [
                'details' => [
                    'Durée' => '2 ans',
                    'Niveau d\'accès' => 'Baccalauréat scientifique',
                    'Objectif' => 'Préparer les étudiants à intégrer un cycle ingénieur dans une grande école.',
                    'Diplôme' => 'La CPGE ne délivre pas un diplôme, mais prépare à l\'admission en école d\'ingénieurs.',
                ],
                'intro' => 'Les enseignements mettent l\'accent sur les mathématiques, la physique, l\'informatique et les sciences de l\'ingénieur afin de préparer l\'entrée dans le cycle ingénieur.',
                'points' => [
                    'Mathématiques',
                    'Physique',
                    'Informatique',
                    'Sciences de l\'ingénieur',
                ],
            ],
        ],
        'licences' => [
            'titre' => 'Licences (Bac+3)',
            'description' => 'Quatre licences professionnalisantes de trois ans combinant cours théoriques, travaux pratiques, projets et stages en entreprise.',
            'contenu' => [
                'programmes' => [
                    [
                        'nom' => 'Licence en Conception des Systèmes d\'Information (CSI)',
                        'objectifs' => [
                            'Développement logiciel',
                            'Bases de données',
                            'Génie logiciel',
                            'Gestion de projet',
                            'Analyse des systèmes d\'information',
                            'Développement Web et Mobile',
                        ],
                        'debouches' => [
                            'Développeur Full Stack',
                            'Analyste programmeur',
                            'Chef de projet junior',
                            'Consultant SI',
                            'Administrateur de bases de données',
                        ],
                    ],
                    [
                        'nom' => 'Licence en Administration Systèmes, Réseaux et Cybersécurité',
                        'objectifs' => [
                            'Administration Linux et Windows',
                            'Réseaux informatiques',
                            'Sécurité informatique',
                            'Virtualisation',
                            'Cloud Computing',
                        ],
                        'debouches' => [
                            'Administrateur systèmes',
                            'Administrateur réseaux',
                            'Analyste cybersécurité',
                            'Technicien sécurité informatique',
                        ],
                    ],
                    [
                        'nom' => 'Licence en Management de la Transition Numérique',
                        'objectifs' => [
                            'Transformation digitale',
                            'Management de projet',
                            'Innovation',
                            'Marketing numérique',
                            'Gouvernance des systèmes d\'information',
                        ],
                        'debouches' => [
                            'Consultant digital',
                            'Chef de projet numérique',
                            'Responsable transformation digitale',
                        ],
                    ],
                    [
                        'nom' => 'Licence en Énergie et Environnement',
                        'objectifs' => [
                            'Production d\'énergie',
                            'Énergies renouvelables',
                            'Développement durable',
                            'Réseaux électriques',
                            'Management énergétique',
                        ],
                        'debouches' => [
                            'Assistant ingénieur',
                            'Consultant énergie',
                            'Chef de projet environnement',
                            'Expert en efficacité énergétique',
                        ],
                    ],
                ],
            ],
        ],
        'cycle-ingenieur' => [
            'titre' => 'Cycle Ingénieur (Bac+5)',
            'description' => 'Après une licence ou une classe préparatoire, les étudiants peuvent intégrer le cycle ingénieur.',
            'contenu' => [
                'objectif' => 'Former des ingénieurs généralistes capables de concevoir des solutions innovantes, piloter des projets complexes, manager des équipes et répondre aux défis industriels et technologiques.',
                'organisation' => [
                    '1ère année : consolidation des bases scientifiques.',
                    '2ème année : spécialisation et projets.',
                    '3ème année : stage long et projet de fin d\'études.',
                ],
                'specialites' => [
                    'Ingénierie du Numérique',
                    'Data Engineering',
                    'Travaux Publics & Éco-cités',
                    'Énergie & Environnement',
                ],
            ],
        ],
        'masters' => [
            'titre' => 'Masters (Bac+5)',
            'description' => 'Les Masters permettent une spécialisation après un Bac+3 ou un diplôme équivalent.',
            'contenu' => [
                'programmes' => [
                    'Master en Data Engineering',
                    'Master en Travaux Publics et Éco-cités',
                    'Master en Énergie et Environnement',
                ],
                'formations' => [
                    'Projets industriels',
                    'Stages',
                    'Entrepreneuriat',
                    'Intelligence artificielle',
                    'Développement durable',
                    'Management de projet',
                ],
            ],
        ],
    ];

    public function index()
    {
        return view('programmes.index', ['categories' => $this->categories]);
    }

    public function show(string $niveau)
    {
        if (! isset($this->categories[$niveau])) {
            abort(404);
        }

        return view('programmes.show', [
            'niveau' => $niveau,
            'category' => $this->categories[$niveau],
        ]);
    }
}
