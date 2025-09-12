<?php

namespace App\Controller;

use App\Entity\Campus;
use App\Form\EmailCheckType;
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
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Twig\Environment;

class RegistrationController extends AbstractController
{
    #[Route('/inscription', name: 'app_register_check_email')]
    public function checkEmail(Request $request, ParticipantRepository $participantRepository, EntityManagerInterface $entityManager, MailerInterface $mailer, Environment $twig): Response
    {
        $form = $this->createForm(EmailCheckType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $email = $form->get('email')->getData();

            // Validate domain
            if (!str_ends_with($email, '@campus-eni.fr') && !str_ends_with($email, '@eni-ecole.fr')) {
                $this->addFlash('error', 'Votre adresse e-mail doit se terminer par @campus-eni.fr ou @eni-ecole.fr.');
                return $this->redirectToRoute('app_register_check_email');
            }

            $participant = $participantRepository->findOneBy(['mail' => $email]);

            if ($participant) {
                // User exists, redirect to login (or display message)
                $this->addFlash('warning', 'Cette adresse e-mail est déjà enregistrée. Veuillez vous connecter.');
                // TODO: Replace with your actual login route if available
                return $this->redirectToRoute('app_login'); // Redirect to home for now, user can then go to login
            }

            // User does not exist, create initial participant and send activation email
            $newParticipant = new \App\Entity\Participant();
            $newParticipant->setMail($email);
            $newParticipant->setIsVerified(false);
            $newParticipant->setActif(false);
            $newParticipant->setRoles(['ROLE_USER']);
            $newParticipant->setMotPasse(null); // Password will be set during activation
            $newParticipant->setPseudo('temp_' . uniqid()); // Set a temporary pseudo

            // Generate activation token and expiry date
            $token = bin2hex(random_bytes(32));
            $newParticipant->setActivationToken($token);
            $newParticipant->setTokenExpiresAt(new \DateTimeImmutable('+1 hour'));

            $entityManager->persist($newParticipant);
            $entityManager->flush();

            // Send activation email
            $emailMessage = (new Email())
                ->from('sortir@ik.me') // Replace with your sender email
                ->to($newParticipant->getMail())
                ->subject('Activez votre compte Sortir.com')
                ->html($twig->render('emails/activation.html.twig', [
                    'participant' => $newParticipant,
                    'token' => $token,
                ]))
                ->text($twig->render('emails/activation.text.twig', [
                    'participant' => $newParticipant,
                    'token' => $token,
                ]));

            $mailer->send($emailMessage);

            $this->addFlash('success', 'Un e-mail d\'activation vous a été envoyé. Veuillez vérifier votre boîte de réception.');
            return $this->redirectToRoute('app_registration_check_email_sent');
        }

        return $this->render('registration/check_email.html.twig', [
            'emailForm' => $form->createView(),
        ]);
    }

    #[Route('/inscription/email-envoye', name: 'app_registration_check_email_sent')]
    public function checkEmailSent(): Response
    {
        return $this->render('registration/check_email_sent.html.twig');
    }

    

    #[Route('/inscription/finaliser/{token}', name: 'app_register_complete_with_token')]
    public function completeRegistrationWithToken(
        string $token,
        ParticipantRepository $participantRepository,
        EntityManagerInterface $entityManager,
        Request $request,
        UserPasswordHasherInterface $userPasswordHasher
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

        if (str_starts_with($participant->getPseudo(), 'temp_')) {
            $participant->setPseudo('');
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
            $participant->setMotPasse(
                $userPasswordHasher->hashPassword(
                    $participant,
                    $form->get('plainPassword')->getData() // Assuming plainPassword is added to RegistrationFormType
                )
            );

            $participant->setIsVerified(true);
            $participant->setActif(true);
            $participant->setActivationToken(null);
            $participant->setTokenExpiresAt(null);

            $entityManager->persist($participant);
            $entityManager->flush();

            $this->addFlash('success', 'Votre compte a été activé et votre mot de passe a été défini avec succès. Vous pouvez maintenant vous connecter.');
            return $this->redirectToRoute('app_login');
        }

        return $this->render('registration/register.html.twig', [
            'registrationForm' => $form->createView(),
            'email' => $participant->getMail(),
        ]);
    }

    
}
