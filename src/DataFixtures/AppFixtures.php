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
        $this->addData($manager);
    }

    public function addData(ObjectManager $manager)
    {


        $faker = Factory::create('fr_FR');


        //Etat
        $tabEtats = ['Créée', 'Ouverte', 'Clôturée', 'Activité en cours', 'Activité terminée', 'Activité archivée', 'Annulée'];

        foreach ($tabEtats as $et){
            $etat = new Etat();
            $etat
                ->setLibelle($et);
            $manager->persist($etat);
            $etats[] = $etat;
        }
        //Ville
        for ($i = 0;
             $i < 20;
             $i++) {
            $ville = new Ville();
            $ville
                ->setNom($faker->realText(20))
                ->setCodePostal($faker->postcode());
            $manager->persist($ville);
            $villes[] = $ville;
        }

        //Lieu
        for ($i = 0; $i < 20; $i++) {
            $lieu = new Lieu();
            $lieu
                ->setNom($faker->realText(25))
                ->setRue($faker->realText(100))
                ->setLatitude($faker->randomFloat())
                ->setLongitude($faker->randomFloat());

            $lieu->setVille($faker->randomElement($villes));
            $lieux[] = $lieu;
            $manager->persist($lieu);

        }

        //Campus
        for ($i = 0; $i < 20; $i++) {
            $campus = new Campus();
            $campus
                ->setNom($faker->realText(20));
            $campusList[] = $campus;
            $manager->persist($campus);

        }



        //Participant
        for ($i = 0; $i < 20; $i++) {
            $participant = new Participant();

            $thePassword = $faker->password();
            $hashedPassword = $this->passwordHasher->hashPassword($participant, $thePassword);

            $participant
                ->setNom($faker->lastName())
                ->setPrenom($faker->firstName())
                ->setTelephone($faker->phoneNumber())
                ->setMail($faker->companyEmail())
                ->setMotPasse($hashedPassword)
                ->setActif($faker->boolean(true))
                ->setOrganisateur($faker->boolean(true));
            $participant->setCampus($faker->randomElement($campusList));
            $participants[] = $participant;
            $manager->persist($participant);

        }


        //Sortie
        for ($i = 0; $i < 20; $i++) {
            $sortie = new Sortie();
            $sortie
                ->setNom($faker->realText(10))
                ->setDateHeureDebut(new \DateTime())
                ->setDuree($faker->randomNumber())
                ->setDateLimiteInscription($faker->dateTime())
                ->setNbInscriptionMax($faker->numberBetween(15, 20))
                ->setInfosSortie($faker->realText(500));
            $sortie
                ->setCampus($faker->randomElement($campusList))
                ->setEtat($faker->randomElement($etats))
                ->setLieu($faker->randomElement($lieux))
                ->addParticipant($faker->randomElement($participants))
                ->setOrganisateur($faker->randomElement($participants));


            $manager->persist($sortie);

        }


        $manager->flush();
    }

}