<?php

namespace Database\Seeders;

use App\Models\Album;
use App\Models\Article;
use App\Models\Institute;
use App\Models\Member;
use App\Models\Message;
use App\Models\OrganizationalUnit;
use App\Models\Project;
use App\Models\Publication;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
// ----- Carrousel des instituts (sous tutelle du MINRESI) -----
        $institutes = [
            ['name' => 'CNDT',     'url' => 'https://www.cndtcameroun.cm/', 'logo' => 'assets/logo-cndt.webp'],
            ['name' => 'ANRP',     'url' => 'http://anrp.cm/', 'logo' => 'assets/instituts/anrp-1.jpg'],
            ['name' => 'IRAD',     'url' => 'http://irad.cm/index.php/fr/', 'logo' => 'assets/instituts/irad-1.jpg'],
            ['name' => 'IMPM',     'url' => 'https://impm-cm.org/', 'logo' => 'assets/instituts/impm.png'],
            ['name' => 'INC',      'url' => 'http://www.inc.ayoos.com/', 'logo' => 'assets/instituts/inc-1.jpg'],
            ['name' => 'MIPROMALO','url' => 'http://www.mipromalo.cm/index.php/fr/', 'logo' => 'assets/instituts/mipromalo-1.jpg'],
            ['name' => 'CNE',      'url' => 'http://195.24.207.248/~minresi/index.php/2015/07/21/accueil_principal/', 'logo' => 'assets/instituts/cne-1.jpeg'],
            ['name' => 'IRGM',     'url' => 'https://irgm-cameroun.org/', 'logo' => 'assets/instituts/irgm-1.jpg'],
        ];

        foreach ($institutes as $i => $inst) {
            // firstOrCreate : le seed n'écrase jamais ce qui a été modifié
            // dans le dashboard (le seed ne s'exécute de toute façon que si
            // DB_SEED=true au premier démarrage).
            Institute::firstOrCreate(
                ['name' => $inst['name']],
                $inst + ['position' => $i + 1, 'is_visible' => true],
            );
        }

        // ----- Comptes -----
        // Le mot de passe vient de la variable d'environnement ADMIN_PASSWORD
        // (jamais de mot de passe en clair dans le dépôt). S'il n'est pas
        // défini, un mot de passe aléatoire est généré et affiché une seule
        // fois dans les logs du conteneur. Un compte existant n'est JAMAIS
        // modifié par le seed.
        $adminPassword = env('ADMIN_PASSWORD') ?: Str::random(24);
        $admin = User::firstOrCreate(
            ['email' => 'admin@cndt.cm'],
            [
                'name' => 'Administrateur CNDT',
                'password' => $adminPassword,
                'is_admin' => true,
            ],
        );
        if ($admin->wasRecentlyCreated && ! env('ADMIN_PASSWORD')) {
            $this->command->warn(
                'Compte admin créé avec le mot de passe généré : '.$adminPassword.
                ' — changez-le immédiatement (ou définissez ADMIN_PASSWORD).'
            );
        }

        // ----- Actualités (contenu réel du site) -----
        // Retire les articles de démonstration initiaux
        Article::whereIn('slug', [
            'lancement-du-programme-national-tic-intelligence-artificielle',
            'seminaire-de-restitution-des-travaux-de-la-commission-technique',
            'nouveau-partenariat-pour-la-recherche-appliquee',
        ])->delete();

        $articles = [
            [
                'slug' => 'fracture-numerique-per-bayangam-akonolinga',
                'title' => "Réduction de la fracture numérique en milieu rural : le MINRESI et le CNDT initient les populations de Bayangam et d'Akonolinga à l'intelligence artificielle",
                'category' => 'formation',
                'excerpt' => "À travers le CNDT, le MINRESI a formé des jeunes, des enseignants et des acteurs locaux aux outils numériques, à la programmation et à l'IA aux Plateformes énergétiques rurales d'Akonolinga et de Bayangam, suivies d'une mission technique de maintenance en juin 2026.",
                'cover_image' => 'assets/per-rural/1.jpg',
                'author_name' => 'M. BABGAI Raphaël GUIDÉKÉ',
                'author_role' => 'Chef de la cellule informatique — CNDT',
                'author_photo' => 'assets/people/babgai-raphael-guidéke.jpeg',
                'author_url' => 'membres/babgai-raphael.html',
                'published_at' => '2026-06-07',
            ],
            [
                'slug' => 'reunion-bilaterale-aiea-vienne',
                'title' => "RÉUNION BILATÉRALE ENTRE LA DÉLÉGATION CAMEROUNAISE ET L'AIEA À VIENNE",
                'category' => 'evenement',
                'excerpt' => "En marge des travaux de la 70e Session Ordinaire de la Conférence Générale de l'AIEA, la Délégation Camerounaise a pris part, le Mercredi 16 septembre 2026, à la Réunion Bilatérale avec les responsables de l'AIEA.",
                'cover_image' => 'assets/reunion-bilaterale-aiea-vienne/1.jpeg',
                'published_at' => '2026-09-16',
            ],
            [
                'slug' => 'aiea70-2026',
                'title' => "Le Cameroun à la 70ᵉ Conférence Générale de l'AIEA",
                'category' => 'evenement',
                'excerpt' => "Sous la présidence de Madame le Ministre de la Recherche Scientifique et de l'Innovation, Dr Madeleine TCHUINTE, le Cameroun a pris part aux travaux de la 70e Session Ordinaire de la Conférence Générale de l'AIEA, du 14 au 18 septembre 2026.",
                'cover_image' => 'assets/aiea70_1.jpeg',
                'published_at' => '2026-09-14',
            ],
            [
                'slug' => 'cndt-43e-session-comite',
                'title' => 'Le Cameroun à la 43ᵉ session du Comité National de Développement des Technologies',
                'category' => 'evenement',
                'excerpt' => "Le Comité National de Développement des Technologies (CNDT) a tenu, ce 09 juillet 2026, la 43ᵉ session de son Comité, présidée par le Dr Madeleine TCHUINTE, Ministre de la Recherche Scientifique et de l'Innovation.",
                'cover_image' => 'assets/comite-43/1.jpg',
                'published_at' => '2026-07-09',
            ],
            [
                'slug' => 'formation-finances-publiques-2026',
                'title' => "Atelier sur l'évolution du cadre légal et réglementaire de la gestion des finances publiques au Cameroun",
                'category' => 'formation',
                'excerpt' => "Les 19 et 20 août 2026, le Comité National de Développement des Technologies (CNDT) a organisé un atelier consacré à l'évolution du cadre légal et réglementaire de la gestion des finances publiques au Cameroun.",
                'cover_image' => 'assets/FormationFInancePublique_1.jpg',
                'published_at' => '2026-08-19',
            ],
            [
                'slug' => 'sia-minresi-2026',
                'title' => "Le CNDT au cœur de la Semaine de l'Intelligence Artificielle 2026",
                'category' => 'ia',
                'excerpt' => "Sous la présidence de Madame le Ministre de la Recherche Scientifique et de l'innovation, Dr Madeleine TCHUINTE, s'est tenue ce mardi 09 juin 2026, dans la salle de conférences du MINRESI, la cérémonie officielle d'ouverture du SIA MINRESI 2026.",
                'cover_image' => 'assets/sia_1.jpg',
                'published_at' => '2026-06-09',
            ],
            [
                'slug' => 'symposium-2026',
                'title' => "Symposium sur l'Intelligence Artificielle et la Souveraineté Numérique au Cameroun",
                'category' => 'ia',
                'excerpt' => "Le lundi 25 mai 2026, le Comité National de Développement des Technologies (CNDT) a organisé, dans la salle de conférences du MINRESI, une journée dédiée à un enjeu stratégique majeur pour le Cameroun.",
                'cover_image' => 'assets/Symposium_1.jpg',
                'published_at' => '2026-05-25',
            ],
            [
                'slug' => 'atelier-ia-recherche-scientifique-2026',
                'title' => "La Recherche Scientifique et l'Intelligence Artificielle Émergente",
                'category' => 'ia',
                'excerpt' => "Le jeudi 21 mai 2026, la salle de conférences du MINRESI a accueilli un atelier de formation sur la recherche scientifique et l'intelligence artificielle émergente, organisé à l'initiative du CNDT.",
                'cover_image' => 'assets/ia-recherche/1.jpg',
                'published_at' => '2026-05-21',
            ],
        ];

        $hero = [
            'sia-minresi-2026' => 1,
            'symposium-2026' => 2,
            'aiea70-2026' => 3,
            'cndt-43e-session-comite' => 4,
            'formation-finances-publiques-2026' => 5,
            'fracture-numerique-per-bayangam-akonolinga' => 6,
        ];

        foreach ($articles as $a) {
            // firstOrCreate par slug : les articles modifiés ou enrichis via
            // le dashboard ne sont jamais écrasés par le seed.
            Article::firstOrCreate(
                ['slug' => $a['slug']],
                [
                    'title' => $a['title'],
                    'category' => $a['category'],
                    'excerpt' => $a['excerpt'],
                    'content' => $a['excerpt'], // contenu complet à enrichir via le dashboard
                    'cover_image' => $a['cover_image'],
                    'author_name' => $a['author_name'] ?? null,
                    'author_role' => $a['author_role'] ?? null,
                    'author_photo' => $a['author_photo'] ?? null,
                    'author_url' => $a['author_url'] ?? null,
                    'in_hero' => isset($hero[$a['slug']]),
                    'hero_position' => $hero[$a['slug']] ?? null,
                    'is_published' => true,
                    'published_at' => $a['published_at'],
                ],
            );
        }

        // ----- Publications -----
        $publications = [
            ['title' => 'Rapport annuel du CNDT', 'type' => 'rapport', 'description' => 'Bilan des activités et orientations stratégiques.'],
            ['title' => 'Communiqué de presse — Session ordinaire', 'type' => 'communique', 'description' => 'Communiqué à l\'issue de la session ordinaire du Conseil.'],
            ['title' => 'Revue Technologies & Développement n°1', 'type' => 'revue', 'description' => 'Revue scientifique et technique du CNDT.'],
        ];

        foreach ($publications as $i => $p) {
            Publication::firstOrCreate(
                ['slug' => Str::slug($p['title'])],
                $p + ['is_published' => true, 'published_at' => now()->subMonths($i)],
            );
        }

        // ----- Personnel : catalogue réel (PersonnelSeeder) -----
        $this->call(PersonnelSeeder::class);

        // ----- Projets -----
        Project::firstOrCreate(
            ['slug' => 'strategie-nationale-tic-ia'],
            [
                'title' => 'Élaboration de la stratégie nationale TIC & IA',
                'status' => 'en_cours',
                'summary' => 'Appui à l\'élaboration et à la mise en œuvre de la stratégie nationale des technologies de l\'information et de l\'intelligence artificielle.',
                'partner' => 'Ministère de la Recherche Scientifique',
                'start_date' => now()->subMonths(3)->toDateString(),
                'is_published' => true,
            ],
        );

        // ----- Galerie -----
        $album = Album::firstOrCreate(
            ['slug' => 'session-ordinaire'],
            [
                'title' => 'Session ordinaire du Conseil',
                'description' => 'Images de la session ordinaire du CNDT.',
                'position' => 1,
            ],
        );
        if ($album->photos()->count() === 0) {
            $album->photos()->create(['path' => 'albums/session-ordinaire/photo-1.jpg', 'position' => 1]);
            $album->photos()->create(['path' => 'albums/session-ordinaire/photo-2.jpg', 'position' => 2]);
        }

        // ----- Organigramme (chapitres I / II / III) -----
        if (OrganizationalUnit::count() === 0) {
            $chapters = [
                ['code' => 'I', 'label' => 'Cabinet du Secrétaire Permanent', 'url' => 'cabinet.html', 'slug' => 'cabinet',
                    'children' => [
                        ['code' => 'I.1', 'label' => 'Secrétariat du Secrétaire Permanent'],
                        ['code' => 'I.2', 'label' => 'Cellule de Suivi, Contrôle et Audit Interne'],
                        ['code' => 'I.3', 'label' => 'Cellule Informatique'],
                        ['code' => 'I.4', 'label' => 'Comptabilité-Matières'],
                        ['code' => 'I.5', 'label' => 'Cellule de Traduction'],
                        ['code' => 'I.6', 'label' => "Cellule de l'Action Sociale & du Multiculturalisme"],
                        ['code' => 'I.7', 'label' => 'Cellule de la Vulgarisation & Transfert'],
                    ]],
                ['code' => 'II', 'label' => 'Commission Opérationnelle', 'slug' => 'commission-operationnelle',
                    'children' => [
                        ['code' => 'II.1', 'label' => 'Commission Recherche & Management des Projets', 'url' => 'crmp.html', 'slug' => 'recherche-projets'],
                        ['code' => 'II.2', 'label' => 'Commission Documentation, Communication & Relations Publiques', 'url' => 'cdcrp.html'],
                        ['code' => 'II.3', 'label' => 'Commission RH, Coopération & Affaires Juridiques', 'url' => 'rhcaj.html'],
                        ['code' => 'II.4', 'label' => 'Commission des Affaires Administratives & Financières', 'url' => 'caaf.html'],
                    ]],
                ['code' => 'III', 'label' => 'Secrétariats des Commissions Techniques', 'url' => 'ct.html', 'slug' => 'commissions-techniques',
                    'children' => [
                        ['code' => '01', 'label' => 'TIC & Intelligence Artificielle'],
                        ['code' => '02', 'label' => 'Technologies & Transformations Industrielles'],
                        ['code' => '03', 'label' => 'Biosciences & Technologies Agricoles'],
                    ]],
            ];

            foreach ($chapters as $cIndex => $chapterData) {
                $children = $chapterData['children'] ?? [];
                unset($chapterData['children']);
                $chapter = OrganizationalUnit::create($chapterData + ['position' => $cIndex + 1]);

                foreach ($children as $uIndex => $unitData) {
                    OrganizationalUnit::create($unitData + ['parent_id' => $chapter->id, 'position' => $uIndex + 1]);
                }
            }
        }

        // ----- Messages de contact -----
        Message::firstOrCreate(
            ['email' => 'visiteur@example.com', 'subject' => 'Demande d\'information'],
            [
                'name' => 'Visiteur Example',
                'message' => 'Bonjour, je souhaite obtenir plus d\'informations sur les activités du CNDT.',
            ],
        );
    }
}
