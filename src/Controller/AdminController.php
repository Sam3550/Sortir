<?php

namespace App\Controller;

use App\Entity\Campus;
use App\Form\AdminRegistrationFormType;
use App\Form\CsvUploadType;
use App\Repository\CampusRepository;
use App\Entity\Participant;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Serializer\Encoder\CsvEncoder;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;
use Symfony\Component\Validator\Validator\ValidatorInterface;

#[Route('/admin')]
#[IsGranted('ROLE_ADMIN')]
final class AdminController extends AbstractController
{
    #[Route(path: '/', name: 'app_admin_index')]
    public function index(): Response
    {
        return $this->render('admin/index.html.twig');
    }

    #[Route('/register', name: 'app_admin_register')]
    public function register(Request $request, UserPasswordHasherInterface $userPasswordHasher, EntityManagerInterface $entityManager): Response
    {
        $user = new Participant();
        $form = $this->createForm(AdminRegistrationFormType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // encode the plain password
            $user->setPassword(
                $userPasswordHasher->hashPassword(
                    $user,
                    $form->get('password')->getData()
                )
            );
            
            $user->setActif(true);
            $user->setIsVerified(true);

            $entityManager->persist($user);
            $entityManager->flush();

            // do anything else you need here, like send an email

            return $this->redirectToRoute('app_admin_index');
        }

                return $this->render('admin/register_csv.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/register-csv', name: 'app_admin_register_csv')]
    public function registerCsv(Request $request, EntityManagerInterface $entityManager, UserPasswordHasherInterface $passwordHasher, CampusRepository $campusRepository, ValidatorInterface $validator): Response
    {
        $form = $this->createForm(CsvUploadType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $csvFile = $form->get('csv_file')->getData();
            
            if ($csvFile) {
                $serializer = new Serializer([new ObjectNormalizer()], [new CsvEncoder()]);
                $data = $serializer->decode(file_get_contents($csvFile), 'csv');

                $createdCount = 0;
                $errors = [];

                foreach ($data as $row) {
                    if (empty($row['nom']) || empty($row['prenom']) || empty($row['mail'])) {
                        $errors[] = 'Ligne ignorée car des informations essentielles (nom, prenom, mail) sont manquantes : ' . json_encode($row);
                        continue;
                    }

                    $user = new Participant();
                    $user->setNom($row['nom']);
                    $user->setPrenom($row['prenom']);
                    $user->setMail($row['mail']);
                    $user->setTelephone($row['telephone'] ?? null);
                    
                    if (!empty($row['campus'])) {
                        $campus = $campusRepository->findOneBy(['nom' => $row['campus']]);
                        if ($campus) {
                            $user->setCampus($campus);
                        }
                    }

                    // Generate a random password
                    $randomPassword = bin2hex(random_bytes(8));
                    $user->setPassword($passwordHasher->hashPassword($user, $randomPassword));

                    $user->setActif(true);
                    $user->setIsVerified(true);
                    $user->setRoles(['ROLE_USER']);

                    $validationErrors = $validator->validate($user);
                    if (count($validationErrors) > 0) {
                        $errors[] = (string) $validationErrors;
                        continue;
                    }

                    $entityManager->persist($user);
                    $createdCount++;
                }

                $entityManager->flush();

                $this->addFlash('success', $createdCount . ' utilisateurs ont été créés avec succès.');
                if (count($errors) > 0) {
                    $this->addFlash('danger', 'Erreurs de validation pour certains utilisateurs : ' . implode(', ', $errors));
                }

                return $this->redirectToRoute('app_admin_index');
            }
        }

        return $this->render('admin/register_csv.html.twig', [
            'form' => $form->createView(),
        ]);
    }
}