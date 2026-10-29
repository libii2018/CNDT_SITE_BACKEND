<?php

namespace Database\Seeders;

use App\Models\Member;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

/**
 * Catalogue réel du personnel (page personnel.html d'origine) :
 *  - les membres « cartes » sont regroupés par niveau hiérarchique ;
 *  - les membres « tableau » alimentent la liste des personnels
 *    (N°, nom, grade, diplôme de recrutement / spécialité).
 */
class PersonnelSeeder extends Seeder
{
    public function run(): void
    {
        // Retire les membres de démonstration du seed initial
        Member::whereIn('slug', ['marie-ngo-bell', 'jean-ekani', 'alice-mvondo', 'paul-atangana'])->delete();

        foreach ($this->catalogue() as $m) {
            $fullName = trim($m['first_name'].' '.$m['last_name']);
            Member::updateOrCreate(
                ['slug' => Str::slug($fullName)],
                $m + ['is_visible' => true],
            );
        }
    }

    private function catalogue(): array
    {
        $cartes = function (string $level, array $personnes): array {
            return array_map(fn ($p) => [
                'first_name' => $p[0],
                'last_name' => $p[1],
                'display_type' => 'card',
                'level' => $level,
                'role' => $p[2],
                'degree' => $p[3],
                'photo' => $p[4] ?? null,
                'page_url' => $p[5] ?? null,
            ], $personnes);
        };

        $table = function (array $personnes): array {
            return array_map(fn ($p) => [
                'first_name' => $p[0],
                'last_name' => $p[1],
                'display_type' => 'table',
                'grade' => 'Attaché de Recherche',
                'speciality' => $p[2],
            ], $personnes);
        };

        return array_merge(
            $cartes('Direction', [
                ['Dr Madeleine', 'TCHUINTE', "Ministre de la Recherche Scientifique et de l'Innovation — Présidente du Comité", 'Docteur', 'assets/people/ministre.jpg', null],
            ]),
            $cartes('Secrétariat Permanent', [
                ['Dr ELE ABIAMA', 'Patrice', 'Secrétaire Permanent du CNDT', 'Directeur de Recherche', 'assets/people/sp.webp', 'membres/ele-abiama-patrice.html'],
            ]),
            $cartes('Coordinations exécutives adjointes', [
                ['Dr TSUANYO', 'David', 'Coordonnateur Exécutif Adjoint chargé de la Recherche', "Doctorat en Ingénierie de l'Énergie", 'assets/people/tsuanyo.webp', 'membres/tsuanyo-david.html'],
                ['M.', 'KOUNA Jean Baptiste', 'Coordonnateur Exécutif Adjoint — Commission Administrative, Financière et Comptable', 'Master Professionnel en Propriété Intellectuelle', 'assets/people/kouna.webp', 'membres/kouna-jean.html'],
                ['Mme', 'BILOUNGA BINDOUA Marie Sandrine', 'Coordonnatrice Exécutive Adjointe chargée de la Coopération', 'Master en Économie · DESS Banque-Finance · Master GRH', 'assets/people/bilounga.webp', 'membres/bilounga-sandrine.html'],
                ['Mme', 'ISAOURA Bénite', 'Coordonnatrice Exécutive Adjointe — Documentation, Communication et Relations Publiques', 'Master Professionnel en Informatique', 'assets/people/isaoura.webp', 'membres/isaoura-benite.html'],
            ]),
            $cartes('Chefs de Secrétariat titulaires', [
                ['Dr', 'ABOUBAKAR EL BOUKAR', 'Chef du Secrétariat des Politiques Scientifiques et Technologiques', 'Doctorat en Droit Public', 'assets/people/el-boukar.webp', 'membres/aboubakar-el-boukar.html'],
                ['M.', 'AMADA BRAHIM', "Chef du Secrétariat des Ressources Humaines, de l'Action Sociale et du Multiculturalisme", 'Doctorant en Biologie', 'assets/people/amada.webp', 'membres/amada-brahim.html'],
                ['Dr MENGA Francis', 'Daniel', "Chef de Secrétariat Technologies de l'Energie des Mines et de l'Environnement", "Doctorat en Énergétique et Environnement", 'assets/people/menga.jpeg', 'membres/menga-francis-daniel.html'],
                ['Dr PAGLAN Paul', 'André', 'Chef du Secrétariat Technologies et Transformations Industrielles', 'Doctorat en Physique Non Linéaire et Systèmes Complexes', 'assets/people/paglan.webp', 'membres/paglan-paul-andre.html'],
                ['Mme', 'TEMGOUA NANFACK Pélagie Flore épse ABOMO', 'Chef du Secrétariat TIC et Intelligence Artificielle', 'Master de Recherche en Informatique', 'assets/people/temgoua.webp', 'membres/temgoua-pelagie.html'],
            ]),
            $cartes('Autres Chefs de Secrétariat', [
                ['Mme', 'EYOUM EKOULE Henriette Annie Dorette', 'Assistante du Secrétaire Permanent', 'Baccalauréat D', 'assets/people/eyoum.webp', 'membres/eyoum-henriette.html'],
                ['M.', 'BABGAI Raphaël GUIDÉKÉ', 'Chef de Secrétariat Adjoint — Responsable du Parc Informatique', 'Master en Informatique et Télécommunications', 'assets/people/babgai-raphael-guidéke.jpeg', 'membres/babgai-raphael.html'],
                ['M.', 'AVAH Jean Jaurès Fils', 'Chef de la cellule informatique', 'DEA en Biologie Animale, option Zoologie', 'assets/people/avah.webp', 'membres/avah-jean-jaures.html'],
            ]),
            $table([
                ['Mme', 'ATEM TAKANG-ETTA Linda-Rose', 'Diplôme d\'Ingénieur en Génie des Procédés Chimiques'],
                ['Mme', 'BALKISSOU Hamidou', "Diplôme d'Ingénieur en Science de l'Environnement — option Génie des Ressources en Eau et Assainissement des Villes Africaines"],
                ['M.', 'BANG NJENJOCK Blixen Bolie', 'Master II en Physique — option Atome et Radiation'],
                ['M.', 'BAYIHA JUNIOR Dorian', 'Diplôme d\'Ingénieur de Conception en Géologie Minière'],
                ['M.', 'DONTSA JIMMY Orlus', 'Diplôme d\'Ingénieur de Conception en Cryptographie'],
                ['M.', 'ELIGUENE FRANCK Gislain', 'Master en Géologie Appliquée'],
                ['M.', 'EPEE WILLIE Aurélein', 'Master en Génie des Procédés et Ingénierie'],
                ['Mme', 'FETMOUFA NJENJI Linda Ange', 'Doctorat Ph.D en Propriété Intellectuelle'],
                ['M.', 'FOTSO FOTSO Guy Phares', 'Doctorat en Mathématiques'],
                ['M.', 'GUIDANA Pierre', "Diplôme d'Ingénieur des Procédés — option Chimie Industrielle et Génie de l'Environnement"],
                ['Mme', 'GUEA BISSAH Christelle', 'Master II en Droit Public'],
                ['Mme', 'HAPSATOU DANLADY', 'Diplôme d\'Ingénieur de Conception en Génie Informatique et Télécommunications'],
                ['M.', 'INOUSS MAMATE Abakaka', 'Diplôme d\'Ingénieur de Conception en Énergies Renouvelables'],
                ['Mme', 'KENMOGNE TCHATCHIM Liliane', 'Master Professionnel en Gouvernance Financière'],
                ['M.', 'KEUTCHANGA FONCHA Jules Marcel', "Diplôme d'Ingénieur de Conception — option Génie Mécanique"],
                ['M.', 'KUATE FOTSO Jean-Marie', 'Master II en Système et Logiciel en Environnement Distribué'],
                ['M.', 'LIBII LI NTEP Joseph Junior', 'Master in Computer Science — option Science des Données'],
                ['M.', 'MABOU DEFO Rodrigue Sommet', 'Master en Modélisation Mathématique'],
                ['M.', 'MAFAI MAGUELVA Nelson', 'Master en Informatique'],
                ['M.', 'MBARGA Hervé Thaddée', "Diplôme d'Ingénieur en Télécommunication et Technologie de l'Information et de la Communication"],
                ['M.', 'MENGUE François Dieudonné', 'Doctorat Ph.D en Physique — option Électronique et Instrumentation'],
                ['Mme', 'MILENE ONDOUA NDONGO', 'Master en Droit Privé'],
                ['Mme', 'NDA ONDOA Elise Natacha', "Diplôme d'Ingénieur de Conception en Management des Risques Industriels et Environnementaux"],
                ['M.', 'NDJAKA Patrick Aimé', "Diplôme d'Ingénieur en Télécommunication et Technologie de l'Information et de la Communication"],
                ['M.', 'NDZANGA ONDOA Luc Claver', 'Master en Mécatronique'],
                ['M.', 'NGIMBUS LAURENT Joël Junior', 'Master en Chimie Industrielle et Environnement'],
                ['Mme', 'NGO NGWEM Marcelline Carine', "Diplôme d'Ingénieur en Chimie Industrielle et Génie de l'Environnement"],
                ['Mme', 'NGOS BIYIHA Erika Mado', 'Master en Physique'],
                ['M.', 'NNANGA NNANGA Jorel Brice', 'Doctorat en Médecine Générale'],
                ['M.', 'NOUBOUOWO CHOUAKE Arthur Stella', "Diplôme d'Ingénieur de Conception en Agronomie et Sciences Agricoles"],
                ['Mme', 'NTEP Juliette Patricia Fanta', "Diplôme d'Ingénieur en Télécommunication — option Sécurité des Réseaux et Systèmes d'Information"],
                ['Mme', 'OKALA BOUILLONG Noelly Manuela', 'Master en Biochimie — option Biotechnologie et Développement'],
                ['Mme', 'ONANA Ebodé', 'Diplôme de Master II en Histoire Économique et Sociale'],
                ['M.', 'OUMAROU DJOUBAIROU Oumarou', "Diplôme d'Ingénieur en Maintenance Industrielle et Productique"],
                ['M.', 'OWONA Jorel Landry', 'Master II en Sciences de la Matière — Électronique, Électrotechnique, Automatique'],
                ['M.', 'SOULEYMANOU MANSOUROU', 'Master en Réseaux et Services Distribués'],
                ['M.', 'TCHATCHOUANG BRAVEL Benveniste', 'Master en Physique'],
                ['Mme', 'TOUEMBOU MAFFO Murielle', 'Master Professionnel en Biologie des Organismes Végétaux'],
                ['M.', 'TSILLA NDONGO Damien Serge', "Diplôme d'Ingénieur en Télécommunication — option Sécurité des Réseaux et Systèmes d'Information"],
                ['Mme', 'YAOUDA M SOPHIE', 'Master en Droit Public'],
            ]),
        );
    }
}
