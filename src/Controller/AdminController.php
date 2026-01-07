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
use App\Security\EmailVerifier;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Mime\Address;

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

        return $this->render('admin/register.html.twig', [
            'registrationForm' => $form->createView(),
        ]);
    }

    #[Route('/register-csv', name: 'app_admin_register_csv')]
    public function registerCsv(
        Request $request, 
        EntityManagerInterface $entityManager, 
        UserPasswordHasherInterface $passwordHasher, 
        CampusRepository $campusRepository, 
        ValidatorInterface $validator,
        EmailVerifier $emailVerifier
    ): Response
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
                    if (empty($row['mail'])) {
                        $errors[] = 'Ligne ignorée car le mail est manquant : ' . json_encode($row);
                        continue;
                    }

                    // Check if user already exists
                    if ($entityManager->getRepository(Participant::class)->findOneBy(['mail' => $row['mail']])) {
                        $errors[] = 'Utilisateur déjà existant : ' . $row['mail'];
                        continue;
                    }

                    $user = new Participant();
                    $user->setMail($row['mail']);
                    // Set temporary placeholders
                    $user->setNom('Invité');
                    $user->setPrenom($row['mail']);
                    // Try to finding campus if provided, otherwise null (or default)
                    if (!empty($row['campus'])) {
                        $campus = $campusRepository->findOneBy(['nom' => $row['campus']]);
                        if ($campus) {
                            $user->setCampus($campus);
                        }
                    }

                    // Random placeholder password (user will define it later)
                    $user->setPassword($passwordHasher->hashPassword($user, bin2hex(random_bytes(16))));

                    $user->setActif(false);
                    $user->setIsVerified(false);
                    $user->setRoles(['ROLE_USER']);

                    $entityManager->persist($user);
                    // Must flush individually to generate ID for the signature
                    $entityManager->flush(); 

                    // Send Magic Link
                    try {
                        $emailVerifier->sendEmailConfirmation('app_magic_register_complete', $user,
                            (new TemplatedEmail())
                                ->from(new Address('contact@funwithsss.fr', 'Sortir.com'))
                                ->to($user->getMail())
                                ->subject('Invitation à rejoindre Sortir.com')
                                ->htmlTemplate('registration/confirmation_email.html.twig')
                        );
                        $createdCount++;
                    } catch (\Exception $e) {
                         $errors[] = "Erreur d'envoi mail pour " . $row['mail'] . ": " . $e->getMessage();
                    }
                }

                $entityManager->flush(); // Final flush just in case (though we did it inside)

                $this->addFlash('success', $createdCount . ' invitations envoyées avec succès.');
                if (count($errors) > 0) {
                    $this->addFlash('danger', 'Erreurs : ' . implode(', ', $errors));
                }

                return $this->redirectToRoute('app_admin_index');
            }
        }

        return $this->render('admin/register_csv.html.twig', [
            'form' => $form->createView(),
        ]);
    }
}