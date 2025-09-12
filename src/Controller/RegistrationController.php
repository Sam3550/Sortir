<?php

namespace App\Controller;

use App\Entity\Campus;

use App\Form\RegistrationFormType;
use App\Repository\ParticipantRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\PasswordHasher\PasswordHasherFactoryInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Twig\Environment;

class RegistrationController extends AbstractController
{
    

    #[Route('/inscription/email-envoye', name: 'app_registration_check_email_sent')]
    public function checkEmailSent(): Response
    {
        return $this->render('registration/check_email_sent.html.twig');
    }

    

    #[Route('/inscription/finaliser/{token}', name: 'app_register')]
    public function completeRegistrationWithToken(
        string $token,
        ParticipantRepository $participantRepository,
        EntityManagerInterface $entityManager,
        Request $request,
        PasswordHasherFactoryInterface $passwordHasherFactory
    ): Response {
        $participant = $participantRepository->findOneBy(['activationToken' => $token]);

        if (!$participant || $participant->isVerified()) {
            $this->addFlash('error', 'Jeton d\'activation invalide ou compte déjà activé.');
            return $this->redirectToRoute('app_home');
        }

        if ($participant->getTokenExpiresAt() < new \DateTimeImmutable()) {
            $this->addFlash('error', 'Le jeton d\'activation a expiré. Veuillez contacter l\'administrateur.');
            return $this->redirectToRoute('app_home');
        }

        $form = $this->createForm(RegistrationFormType::class, $participant);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Set default campus to Saint-Herblain if not already set
            if (!$participant->getCampus()) {
                $saintHerblainCampus = $entityManager->getRepository(Campus::class)->findOneBy(['nom' => 'Saint-Herblain']);
                if ($saintHerblainCampus) {
                    $participant->setCampus($saintHerblainCampus);
                }
            }

            // Encode the plain password
            $hasher = $passwordHasherFactory->getPasswordHasher($participant);
            $participant->setMotPasse(
                $hasher->hash($form->get('plainPassword')->getData())
            );

            $participant->setIsVerified(true);
            $participant->setActif(true); // Mark as active
            $participant->setActivationToken(null); // Clear token
            $participant->setTokenExpiresAt(null); // Clear expiry date

            $entityManager->persist($participant);
            $entityManager->flush();

            $this->addFlash('success', 'Votre compte a été activé et votre mot de passe a été défini avec succès. Vous pouvez maintenant vous connecter.');
            return $this->redirectToRoute('app_login'); // Redirect to login page
        }

        return $this->render('registration/register.html.twig', [
            'registrationForm' => $form->createView(),
            'email' => $participant->getMail(), // Pass email to the template
        ]);
    }

    #[Route('/check-pseudo', name: 'app_check_pseudo', methods: ['GET'])]
    public function checkPseudo(Request $request, ParticipantRepository $participantRepository): JsonResponse
    {
        dump($request);
        die('Request reached checkPseudo method.');
        // return new JsonResponse(['isUnique' => true, 'message' => 'Test response.']);
    }
}
