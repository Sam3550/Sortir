<?php

namespace App\DataFixtures;

use App\Entity\Campus;
use App\Entity\Etat;
use App\Entity\Lieu;
use App\Entity\Participant;
use App\Entity\Sortie;
use App\Entity\Ville;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class AppFixtures extends Fixture
{
    private UserPasswordHasherInterface $passwordHasher;

    public function __construct(UserPasswordHasherInterface $passwordHasher)
    {
        $this->passwordHasher = $passwordHasher;
    }

    public function load(ObjectManager $manager): void
    {
        // ========================================
        // ETATS (obligatoires pour le fonctionnement)
        // ========================================
        $tabEtats = [
            Etat::CREEE,
            Etat::OUVERTE,
            Etat::CLOTURE,
            Etat::ACTENCOURS,
            Etat::ACTTERMINER,
            Etat::ACTARCHIVE,
            Etat::ANNULE
        ];

        $etats = [];
        foreach ($tabEtats as $libelle) {
            $etat = new Etat();
            $etat->setLibelle($libelle);
            $manager->persist($etat);
            $etats[$libelle] = $etat;
        }

        // ========================================
        // CAMPUS ENI (écoles réelles)
        // ========================================
        $campusData = [
            'ENI Nantes',
            'ENI Rennes',
            'ENI Quimper',
            'ENI Niort',
            'ENI La Roche-sur-Yon'
        ];

        $campusList = [];
        foreach ($campusData as $nom) {
            $campus = new Campus();
            $campus->setNom($nom);
            $manager->persist($campus);
            $campusList[$nom] = $campus;
        }
        $manager->flush(); // Flush pour s'assurer que les campus sont en base

        // ========================================
        // VILLES (villes réelles de l'Ouest)
        // ========================================
        $villesData = [
            ['nom' => 'Nantes', 'codePostal' => '44000'],
            ['nom' => 'Rennes', 'codePostal' => '35000'],
            ['nom' => 'Quimper', 'codePostal' => '29000'],
            ['nom' => 'Niort', 'codePostal' => '79000'],
            ['nom' => 'La Roche-sur-Yon', 'codePostal' => '85000'],
            ['nom' => 'Saint-Nazaire', 'codePostal' => '44600'],
            ['nom' => 'Angers', 'codePostal' => '49000'],
            ['nom' => 'Le Mans', 'codePostal' => '72000'],
            ['nom' => 'Vannes', 'codePostal' => '56000'],
            ['nom' => 'Lorient', 'codePostal' => '56100'],
        ];

        $villes = [];
        foreach ($villesData as $data) {
            $ville = new Ville();
            $ville->setNom($data['nom']);
            $ville->setCodePostal($data['codePostal']);
            $manager->persist($ville);
            $villes[$data['nom']] = $ville;
        }

        // ========================================
        // LIEUX (lieux réels avec vraies coordonnées GPS)
        // ========================================
        $lieuxData = [
            ['nom' => 'Parc de Procé', 'rue' => 'Boulevard des Anglais', 'lat' => 47.2284, 'lng' => -1.5755, 'ville' => 'Nantes'],
            ['nom' => 'Bowling de Nantes', 'rue' => '15 Rue de la Jonelière', 'lat' => 47.2521, 'lng' => -1.5480, 'ville' => 'Nantes'],
            ['nom' => 'Cinéma Gaumont', 'rue' => '12 Place du Commerce', 'lat' => 47.2133, 'lng' => -1.5578, 'ville' => 'Nantes'],
            ['nom' => 'Escape Game The Game', 'rue' => '3 Rue Racine', 'lat' => 47.2167, 'lng' => -1.5514, 'ville' => 'Nantes'],
            ['nom' => 'La Cigale', 'rue' => '4 Place Graslin', 'lat' => 47.2134, 'lng' => -1.5632, 'ville' => 'Nantes'],
            ['nom' => 'Parc du Thabor', 'rue' => 'Place Saint-Melaine', 'lat' => 48.1127, 'lng' => -1.6693, 'ville' => 'Rennes'],
            ['nom' => 'Laser Game Evolution', 'rue' => '2 Rue de Lorraine', 'lat' => 48.1052, 'lng' => -1.6780, 'ville' => 'Rennes'],
            ['nom' => 'Piscine Saint-Georges', 'rue' => '2 Rue Gambetta', 'lat' => 48.1147, 'lng' => -1.6826, 'ville' => 'Rennes'],
            ['nom' => 'Le Triangle', 'rue' => 'Boulevard de Yougoslavie', 'lat' => 48.1089, 'lng' => -1.6754, 'ville' => 'Rennes'],
            ['nom' => 'Stade Rennais', 'rue' => '111 Route de Lorient', 'lat' => 48.1075, 'lng' => -1.7126, 'ville' => 'Rennes'],
            ['nom' => 'Plage de Bénodet', 'rue' => 'Boulevard de la Mer', 'lat' => 47.8754, 'lng' => -4.1085, 'ville' => 'Quimper'],
            ['nom' => 'Halles de Quimper', 'rue' => 'Rue Astor', 'lat' => 47.9960, 'lng' => -4.1028, 'ville' => 'Quimper'],
            ['nom' => 'Accrobranche Niort', 'rue' => 'Chemin de Bessines', 'lat' => 46.3229, 'lng' => -0.4562, 'ville' => 'Niort'],
            ['nom' => 'Marais Poitevin', 'rue' => 'Port du Bec', 'lat' => 46.2896, 'lng' => -0.5847, 'ville' => 'Niort'],
            ['nom' => 'Base nautique', 'rue' => 'Lac de la Moulinette', 'lat' => 46.6628, 'lng' => -1.4269, 'ville' => 'La Roche-sur-Yon'],
        ];

        $lieux = [];
        foreach ($lieuxData as $data) {
            $lieu = new Lieu();
            $lieu->setNom($data['nom']);
            $lieu->setRue($data['rue']);
            $lieu->setLatitude($data['lat']);
            $lieu->setLongitude($data['lng']);
            $lieu->setVille($villes[$data['ville']]);
            $manager->persist($lieu);
            $lieux[] = $lieu;
        }

        // ========================================
        // PARTICIPANTS (utilisateurs réalistes)
        // ========================================
        $participantsData = [
            ['nom' => 'Martin', 'prenom' => 'Sophie', 'mail' => 'sophie.martin@campus-eni.fr', 'tel' => '06 12 34 56 78', 'campus' => 'ENI Nantes'],
            ['nom' => 'Durand', 'prenom' => 'Pierre', 'mail' => 'pierre.durand@campus-eni.fr', 'tel' => '06 23 45 67 89', 'campus' => 'ENI Nantes'],
            ['nom' => 'Bernard', 'prenom' => 'Marie', 'mail' => 'marie.bernard@campus-eni.fr', 'tel' => '06 34 56 78 90', 'campus' => 'ENI Nantes'],
            ['nom' => 'Petit', 'prenom' => 'Lucas', 'mail' => 'lucas.petit@campus-eni.fr', 'tel' => '06 45 67 89 01', 'campus' => 'ENI Nantes'],
            ['nom' => 'Robert', 'prenom' => 'Emma', 'mail' => 'emma.robert@campus-eni.fr', 'tel' => '06 56 78 90 12', 'campus' => 'ENI Nantes'],
            ['nom' => 'Richard', 'prenom' => 'Thomas', 'mail' => 'thomas.richard@campus-eni.fr', 'tel' => '06 67 89 01 23', 'campus' => 'ENI Rennes'],
            ['nom' => 'Moreau', 'prenom' => 'Léa', 'mail' => 'lea.moreau@campus-eni.fr', 'tel' => '06 78 90 12 34', 'campus' => 'ENI Rennes'],
            ['nom' => 'Simon', 'prenom' => 'Hugo', 'mail' => 'hugo.simon@campus-eni.fr', 'tel' => '06 89 01 23 45', 'campus' => 'ENI Rennes'],
            ['nom' => 'Laurent', 'prenom' => 'Chloé', 'mail' => 'chloe.laurent@campus-eni.fr', 'tel' => '06 90 12 34 56', 'campus' => 'ENI Rennes'],
            ['nom' => 'Michel', 'prenom' => 'Nathan', 'mail' => 'nathan.michel@campus-eni.fr', 'tel' => '06 01 23 45 67', 'campus' => 'ENI Quimper'],
            ['nom' => 'Garcia', 'prenom' => 'Camille', 'mail' => 'camille.garcia@campus-eni.fr', 'tel' => '06 11 22 33 44', 'campus' => 'ENI Quimper'],
            ['nom' => 'David', 'prenom' => 'Maxime', 'mail' => 'maxime.david@campus-eni.fr', 'tel' => '06 22 33 44 55', 'campus' => 'ENI Niort'],
            ['nom' => 'Bertrand', 'prenom' => 'Julie', 'mail' => 'julie.bertrand@campus-eni.fr', 'tel' => '06 33 44 55 66', 'campus' => 'ENI Niort'],
            ['nom' => 'Roux', 'prenom' => 'Antoine', 'mail' => 'antoine.roux@campus-eni.fr', 'tel' => '06 44 55 66 77', 'campus' => 'ENI La Roche-sur-Yon'],
            ['nom' => 'Fournier', 'prenom' => 'Manon', 'mail' => 'manon.fournier@campus-eni.fr', 'tel' => '06 55 66 77 88', 'campus' => 'ENI La Roche-sur-Yon'],
        ];

        $participants = [];
        foreach ($participantsData as $index => $data) {
            $participant = new Participant();
            $hashedPassword = $this->passwordHasher->hashPassword($participant, '123456');

            $campusName = $data['campus'];
            if (!isset($campusList[$campusName])) {
                throw new \RuntimeException("Campus '$campusName' non trouvé pour participant index $index. Disponibles: " . implode(', ', array_keys($campusList)));
            }

            $participant
                ->setNom($data['nom'])
                ->setPrenom($data['prenom'])
                ->setMail($data['mail'])
                ->setTelephone($data['tel'])
                ->setMotPasse($hashedPassword)
                ->setActif(true)
                ->setCampus($campusList[$campusName])
                ->setRoles(['ROLE_USER'])
                ->setIsVerified(true);
            
            $participant->setOrganisateur(false); // Appel séparé car renvoie void

            $manager->persist($participant);
            $participants[] = $participant;
        }

        // ========================================
        // SORTIES (activités réalistes)
        // ========================================
        $sortiesData = [
            [
                'nom' => 'Bowling du vendredi',
                'description' => 'Soirée bowling entre collègues de promo ! Ambiance décontractée, bonne humeur garantie. Prévoir des chaussettes pour les chaussures de bowling.',
                'dateDebut' => '+7 days 19:00',
                'dateLimite' => '+5 days',
                'duree' => 180,
                'nbMax' => 12,
                'lieu' => 1, // Bowling de Nantes
                'campus' => 'ENI Nantes',
                'etat' => Etat::OUVERTE,
                'organisateur' => 0,
                'inscrits' => [1, 2, 3]
            ],
            [
                'nom' => 'Randonnée au Parc de Procé',
                'description' => 'Balade matinale dans le magnifique Parc de Procé. Parfait pour se détendre avant les cours. Prévoir des chaussures confortables et une bouteille d\'eau.',
                'dateDebut' => '+3 days 09:00',
                'dateLimite' => '+2 days',
                'duree' => 120,
                'nbMax' => 20,
                'lieu' => 0, // Parc de Procé
                'campus' => 'ENI Nantes',
                'etat' => Etat::OUVERTE,
                'organisateur' => 1,
                'inscrits' => [0, 2, 4]
            ],
            [
                'nom' => 'Ciné - Dernier Marvel',
                'description' => 'On va voir le dernier film Marvel ensemble ! RDV devant le cinéma 15 minutes avant la séance. Pop-corn offert par l\'orga !',
                'dateDebut' => '+10 days 20:30',
                'dateLimite' => '+8 days',
                'duree' => 150,
                'nbMax' => 15,
                'lieu' => 2, // Cinéma Gaumont
                'campus' => 'ENI Nantes',
                'etat' => Etat::OUVERTE,
                'organisateur' => 2,
                'inscrits' => [0, 1]
            ],
            [
                'nom' => 'Escape Game en équipe',
                'description' => 'Venez tester vos méninges dans cet escape game réputé ! Thème : Enquête policière. Équipes de 4-5 personnes.',
                'dateDebut' => '+14 days 14:00',
                'dateLimite' => '+12 days',
                'duree' => 90,
                'nbMax' => 10,
                'lieu' => 3, // Escape Game
                'campus' => 'ENI Nantes',
                'etat' => Etat::OUVERTE,
                'organisateur' => 3,
                'inscrits' => [0, 1, 2, 4]
            ],
            [
                'nom' => 'Restaurant La Cigale',
                'description' => 'Repas de fin de module dans la célèbre brasserie La Cigale ! Menu à 25€ (entrée + plat + dessert). Réservation confirmée.',
                'dateDebut' => '+21 days 12:30',
                'dateLimite' => '+18 days',
                'duree' => 120,
                'nbMax' => 18,
                'lieu' => 4, // La Cigale
                'campus' => 'ENI Nantes',
                'etat' => Etat::CREEE,
                'organisateur' => 4,
                'inscrits' => []
            ],
            [
                'nom' => 'Pique-nique au Thabor',
                'description' => 'Pique-nique convivial dans le magnifique Parc du Thabor. Chacun apporte quelque chose à partager. Jeux de société prévus !',
                'dateDebut' => '+5 days 12:00',
                'dateLimite' => '+4 days',
                'duree' => 180,
                'nbMax' => 25,
                'lieu' => 5, // Parc du Thabor
                'campus' => 'ENI Rennes',
                'etat' => Etat::OUVERTE,
                'organisateur' => 5,
                'inscrits' => [6, 7, 8]
            ],
            [
                'nom' => 'Laser Game',
                'description' => 'Session laser game intense ! Deux équipes, un seul vainqueur. Prévoir des vêtements sombres et confortables.',
                'dateDebut' => '+8 days 18:00',
                'dateLimite' => '+6 days',
                'duree' => 120,
                'nbMax' => 16,
                'lieu' => 6, // Laser Game Evolution
                'campus' => 'ENI Rennes',
                'etat' => Etat::OUVERTE,
                'organisateur' => 6,
                'inscrits' => [5, 7, 8]
            ],
            [
                'nom' => 'Aquagym à la piscine',
                'description' => 'Séance d\'aquagym pour tous niveaux à la Piscine Saint-Georges. Maillot de bain et bonnet obligatoires.',
                'dateDebut' => '+4 days 17:30',
                'dateLimite' => '+3 days',
                'duree' => 60,
                'nbMax' => 12,
                'lieu' => 7, // Piscine Saint-Georges
                'campus' => 'ENI Rennes',
                'etat' => Etat::CLOTURE,
                'organisateur' => 7,
                'inscrits' => [5, 6, 8]
            ],
            [
                'nom' => 'Concert au Triangle',
                'description' => 'Concert de musique électronique au Triangle. Places réservées, on y va ensemble !',
                'dateDebut' => '+15 days 21:00',
                'dateLimite' => '+10 days',
                'duree' => 240,
                'nbMax' => 8,
                'lieu' => 8, // Le Triangle
                'campus' => 'ENI Rennes',
                'etat' => Etat::OUVERTE,
                'organisateur' => 8,
                'inscrits' => [5, 6]
            ],
            [
                'nom' => 'Match de foot - Stade Rennais',
                'description' => 'On va supporter le Stade Rennais ! Match de Ligue 1 contre l\'OM. Ambiance garantie en tribune Vilaine.',
                'dateDebut' => '+20 days 17:00',
                'dateLimite' => '+15 days',
                'duree' => 150,
                'nbMax' => 10,
                'lieu' => 9, // Stade Rennais
                'campus' => 'ENI Rennes',
                'etat' => Etat::OUVERTE,
                'organisateur' => 5,
                'inscrits' => [6, 7]
            ],
            [
                'nom' => 'Journée plage à Bénodet',
                'description' => 'Journée détente à la plage de Bénodet ! Covoiturage organisé depuis le campus. Prévoir crème solaire et serviette.',
                'dateDebut' => '+12 days 10:00',
                'dateLimite' => '+10 days',
                'duree' => 480,
                'nbMax' => 15,
                'lieu' => 10, // Plage de Bénodet
                'campus' => 'ENI Quimper',
                'etat' => Etat::OUVERTE,
                'organisateur' => 9,
                'inscrits' => [10]
            ],
            [
                'nom' => 'Marché des Halles',
                'description' => 'Découverte des produits locaux aux Halles de Quimper. Dégustation de crêpes prévue !',
                'dateDebut' => '+6 days 09:30',
                'dateLimite' => '+5 days',
                'duree' => 120,
                'nbMax' => 10,
                'lieu' => 11, // Halles de Quimper
                'campus' => 'ENI Quimper',
                'etat' => Etat::OUVERTE,
                'organisateur' => 10,
                'inscrits' => [9]
            ],
            [
                'nom' => 'Accrobranche',
                'description' => 'Parcours accrobranche pour les plus courageux ! Différents niveaux de difficulté disponibles.',
                'dateDebut' => '+9 days 14:00',
                'dateLimite' => '+7 days',
                'duree' => 180,
                'nbMax' => 12,
                'lieu' => 12, // Accrobranche Niort
                'campus' => 'ENI Niort',
                'etat' => Etat::OUVERTE,
                'organisateur' => 11,
                'inscrits' => [12]
            ],
            [
                'nom' => 'Balade en barque - Marais Poitevin',
                'description' => 'Découverte de la Venise Verte en barque traditionnelle. Paysages magnifiques garantis !',
                'dateDebut' => '+11 days 10:00',
                'dateLimite' => '+9 days',
                'duree' => 180,
                'nbMax' => 8,
                'lieu' => 13, // Marais Poitevin
                'campus' => 'ENI Niort',
                'etat' => Etat::OUVERTE,
                'organisateur' => 12,
                'inscrits' => [11]
            ],
            [
                'nom' => 'Paddle sur le lac',
                'description' => 'Initiation au paddle sur le lac de la Moulinette. Matériel fourni, maillot de bain conseillé !',
                'dateDebut' => '+13 days 15:00',
                'dateLimite' => '+11 days',
                'duree' => 120,
                'nbMax' => 10,
                'lieu' => 14, // Base nautique
                'campus' => 'ENI La Roche-sur-Yon',
                'etat' => Etat::OUVERTE,
                'organisateur' => 13,
                'inscrits' => [14]
            ],
        ];

        foreach ($sortiesData as $data) {
            $sortie = new Sortie();
            $sortie
                ->setNom($data['nom'])
                ->setInfosSortie($data['description'])
                ->setDateHeureDebut(new \DateTime($data['dateDebut']))
                ->setDateLimiteInscription(new \DateTime($data['dateLimite']))
                ->setDuree($data['duree'])
                ->setNbInscriptionMax($data['nbMax'])
                ->setLieu($lieux[$data['lieu']])
                ->setCampus($campusList[$data['campus']])
                ->setEtat($etats[$data['etat']])
                ->setOrganisateur($participants[$data['organisateur']]);

            // Ajouter les participants inscrits
            foreach ($data['inscrits'] as $inscritIndex) {
                $sortie->addParticipant($participants[$inscritIndex]);
            }

            $manager->persist($sortie);
        }

        $manager->flush();
    }
}