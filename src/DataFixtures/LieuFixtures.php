<?php

namespace App\DataFixtures;

use App\Entity\Lieu;
use App\Entity\Ville;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;

class LieuFixtures extends Fixture implements DependentFixtureInterface
{
    public function load(ObjectManager $manager): void
    {
        $faker = Factory::create('fr_FR');

        $villes = $manager->getRepository(Ville::class)->findAll();

        if (empty($villes)) {
            throw new \RuntimeException('No Ville entities found. Please load AppFixtures first.');
        }

        for ($i = 0; $i < 10; $i++) {
            $lieu = new Lieu();
            $lieu->setNom($faker->company)
                ->setRue($faker->streetAddress)
                ->setLatitude($faker->latitude(46, 48))
                ->setLongitude($faker->longitude(-2, 0))
                ->setVille($faker->randomElement($villes));

            $manager->persist($lieu);
        }

        $manager->flush();
    }

    public function getDependencies(): array
    {
        return [
            AppFixtures::class, // Assuming AppFixtures creates Ville entities
        ];
    }
}
