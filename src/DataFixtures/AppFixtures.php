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
use Faker\Factory;
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
        echo "\n--- Starting AppFixtures ---\n";

        // 1. Purge with DELETE to respect transactions
        $connection = $manager->getConnection();
        $connection->executeStatement('SET FOREIGN_KEY_CHECKS=0;');
        $tables = ['participant_sortie', 'sortie', 'lieu', 'participant', 'campus', 'ville', 'etat'];
        foreach ($tables as $table) {
            $connection->executeStatement('DELETE FROM ' . $table);
            echo "Purged table: " . $table . "\n";
        }
        $connection->executeStatement('SET FOREIGN_KEY_CHECKS=1;');
        echo "Purge complete.\n";

        $faker = Factory::create('fr_FR');

        // 2. Create Data
        $villes = [];
        $villesData = [
            ['nom' => 'Nantes', 'codePostal' => '44000'],
            ['nom' => 'Rennes', 'codePostal' => '35000'],
            ['nom' => 'Niort', 'codePostal' => '79000'],
        ];
        foreach ($villesData as $data) {
            $ville = new Ville();
            $ville->setNom($data['nom'])->setCodePostal($data['codePostal']);
            $manager->persist($ville);
            $villes[] = $ville;
        }
        echo "Created " . count($villes) . " Villes.\n";
        $manager->flush(); // Flush Villes to make them findable

        $campuses = [];
        $campusData = ['Saint-Herblain' => $villes[0], 'Chartres-de-Bretagne' => $villes[1], 'Niort' => $villes[2]];
        foreach ($campusData as $nom => $ville) {
            $campus = new Campus();
            $campus->setNom($nom);
            $manager->persist($campus);
            $campuses[] = $campus;
        }
        echo "Created " . count($campuses) . " Campuses.\n";

        $admin = new Participant();
        $admin->setMail('admin@campus-eni.fr')
            ->setRoles(['ROLE_ADMIN'])
            ->setPassword($this->passwordHasher->hashPassword($admin, '123456'))
            ->setNom('Admin')
            ->setPrenom('User')
            ->setActif(true)
            ->setIsVerified(true)
            ->setCampus($campuses[0]);
        $manager->persist($admin);
        echo "Created Admin user.\n";

        $allLieux = [];
        $lieuxData = [
            'Nantes' => [['nom' => 'Cinéma Gaumont', 'rue' => 'Place du Commerce', 'lat' => 47.2136, 'lon' => -1.5546]],
            'Rennes' => [['nom' => 'Parc du Thabor', 'rue' => 'Place Saint-Mélaine', 'lat' => 48.1149, 'lon' => -1.6718]],
            'Niort' => [['nom' => 'Le Moulin du Roc', 'rue' => '9 Boulevard Main', 'lat' => 46.326, 'lon' => -0.458]]
        ];
        foreach ($lieuxData as $villeNom => $lieuxVille) {
            $ville = $manager->getRepository(Ville::class)->findOneBy(['nom' => $villeNom]);
            if ($ville) {
                foreach ($lieuxVille as $lieuData) {
                    $lieu = new Lieu();
                    $lieu->setNom($lieuData['nom'])->setRue($lieuData['rue'])->setLatitude($lieuData['lat'])->setLongitude($lieuData['lon'])->setVille($ville);
                    $manager->persist($lieu);
                    $allLieux[] = $lieu;
                }
            }
        }
        echo "Created " . count($allLieux) . " Lieux.\n";

        $allParticipants = [$admin];
        foreach ($campuses as $campus) {
            for ($i = 0; $i < 10; $i++) {
                $participant = new Participant();
                $participant->setNom($faker->lastName)
                    ->setPrenom($faker->firstName)
                    ->setMail($faker->email)
                    ->setPassword($this->passwordHasher->hashPassword($participant, '123456'))
                    ->setActif(true)
                    ->setIsVerified(true)
                    ->setCampus($campus);
                $manager->persist($participant);
                $allParticipants[] = $participant;
            }
        }
        echo "Created " . count($allParticipants) . " Participants (including admin).\n";

        $etats = [];
        $etatsData = ['Créée', 'Ouverte', 'Clôturée', 'En cours', 'Terminée', 'Annulée'];
        foreach ($etatsData as $libelle) {
            $etat = new Etat();
            $etat->setLibelle($libelle);
            $manager->persist($etat);
            $etats[$libelle] = $etat;
        }
        echo "Created " . count($etats) . " Etats.\n";
        
        $manager->flush(); // Flush everything before creating sorties
        echo "First flush complete.\n";

        $sortiesCreated = 0;
        for ($i = 0; $i < 50; $i++) {
            echo "\n--- Sortie loop iteration " . ($i + 1) . " ---\n";
            $sortie = new Sortie();
            $campus = $faker->randomElement($campuses);
            echo "Selected Campus: " . $campus->getNom() . "\n";

            $participantsDuCampus = array_filter($allParticipants, fn($p) => $p->getCampus() === $campus);
            echo "Participants for Campus (count): " . count($participantsDuCampus) . "\n";
            if (empty($participantsDuCampus)) {
                echo "Skipping: No participants for this campus.\n";
                continue; // Skip if no participants for this campus
            }
            $organisateur = $faker->randomElement($participantsDuCampus);
            echo "Selected Organizer: " . $organisateur->getMail() . "\n";

            $villeAssociee = $campusData[$campus->getNom()];
            echo "Associated Ville: " . $villeAssociee->getNom() . "\n";
            $lieuxPossibles = array_filter($allLieux, fn($l) => $l->getVille() === $villeAssociee);
            echo "Possible Lieux (count): " . count($lieuxPossibles) . "\n";
            if (empty($lieuxPossibles)) {
                echo "Skipping: No lieux for this city.\n";
                continue; // Skip if no lieux for this city
            }
            $lieu = $faker->randomElement($lieuxPossibles);
            echo "Selected Lieu: " . $lieu->getNom() . "\n";

            $dateDebut = $faker->dateTimeBetween('-2 months', '+2 months');
            $dateLimite = (clone $dateDebut)->modify('-' . $faker->numberBetween(3, 10) . ' days');
            $nbMaxInscriptions = $faker->numberBetween(5, 20);

            $sortie->setNom($faker->sentence(4))
                ->setCampus($campus)
                ->setLieu($lieu)
                ->setOrganisateur($organisateur)
                ->addParticipant($organisateur)
                ->setNbInscriptionMax($nbMaxInscriptions)
                ->setDateHeureDebut($dateDebut)
                ->setDateLimiteInscription($dateLimite)
                ->setDuree($faker->numberBetween(60, 300))
                ->setInfosSortie($faker->paragraphs(3, true));

            $etatFinal = $etats['Ouverte'];
            if ($dateDebut < new \DateTime()) $etatFinal = $etats['Terminée'];
            elseif ($dateLimite < new \DateTime()) $etatFinal = $etats['Clôturée'];

            $sortie->setEtat($etatFinal);
            $manager->persist($sortie);
            $sortiesCreated++;
            echo "Sortie persisted: " . $sortie->getNom() . "\n";
        }

        $manager->flush(); // Final flush
        echo "Final flush complete. Total sorties created: " . $sortiesCreated . "\n";
        echo "--- AppFixtures finished ---\n";
    }
}