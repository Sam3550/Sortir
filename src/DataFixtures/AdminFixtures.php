<?php

namespace App\DataFixtures;

use App\Entity\Campus;
use App\Entity\Participant;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class AdminFixtures extends Fixture
{
    private UserPasswordHasherInterface $passwordHasher;

    public function __construct(UserPasswordHasherInterface $passwordHasher)
    {
        $this->passwordHasher = $passwordHasher;
    }

    public function load(ObjectManager $manager): void
    {
        $admin = new Participant();
        $admin->setMail('admin@campus-eni.fr');
        $admin->setRoles(['ROLE_ADMIN']);
        $admin->setPassword(
            $this->passwordHasher->hashPassword(
                $admin,
                '123456'
            )
        );
        $admin->setNom('Admin');
        $admin->setPrenom('User');
        $admin->setPseudo('admin');
        $admin->setActif(true);
        $admin->setIsVerified(true);

        // Try to find a campus to associate with the admin user
        $campusRepository = $manager->getRepository(Campus::class);
        $campus = $campusRepository->findOneBy([]);
        if ($campus) {
            $admin->setCampus($campus);
        }

        $manager->persist($admin);
        $manager->flush();
    }
}
