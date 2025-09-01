<?php

namespace App\DataFixtures;

use App\Entity\Serie;
use App\Entity\Sortie;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;

class AppFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {

        $faker = Factory::create('fr_FR');

        for ($i = 0; $i < 20; $i++) {

            $sortie = new Sortie();
            $sortie
                ->setBackdrop($faker->realText(10))
                ->setDateCreated(new \DateTime())
                ->setGenres($faker->randomElement(['Western', 'SF', 'Drama', 'Comedy']))
                ->setName($faker->realText(10))
                ->setNbLike($faker->numberBetween(0, 500))
                ->setFirstAirDate($faker->dateTimeBetween('-6 year'));
        }

        $manager->flush();
    }
}
