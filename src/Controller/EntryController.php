<?php

namespace App\Controller;

use App\Entity\Participant;
use App\Form\EmailCheckType;
use App\Repository\ParticipantRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\MailerInterface;
use App\Form\PasswordLoginType;
use Symfony\Component\Security\Bundle\Security;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Mime\Email;
use Twig\Environment;
use Symfony\Component\Routing\Attribute\Route;

class EntryController extends AbstractController
{
    #[Route('/entry', name: 'app_entry', methods: ['GET', 'POST'])]
    public function entry(
        Request $request,
        ParticipantRepository $participantRepository,
        EntityManagerInterface $entityManager,
        MailerInterface $mailer,
        Environment $twig
    ): Response
    {
        // If user is already logged in, redirect them
        if ($this->getUser()) {
            return $this->redirectToRoute('sortie_list');
        }

        $form = $this->createForm(EmailCheckType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $email = $form->get('email')->getData();
            $participant = $participantRepository->findOneBy(['mail' => $email]);

            if ($participant) {
                // User exists, store email in session and redirect to password step
                $request->getSession()->set('login_email', $email);
                return $this->redirectToRoute('app_entry_password');
            } else {
                // User does not exist, check domain and start registration
                if (!str_ends_with($email, '@campus-eni.fr') && !str_ends_with($email, '@eni-ecole.fr')) {
                    $this->addFlash('error', 'Votre adresse e-mail doit se terminer par @campus-eni.fr ou @eni-ecole.fr pour vous inscrire.');
                    return $this->redirectToRoute('app_entry');
                }

                // This logic is duplicated from RegistrationController, consider moving to a service
                $newParticipant = new \App\Entity\Participant();
                $newParticipant->setMail($email);
                $newParticipant->setIsVerified(false);
                $newParticipant->setActif(false); // Ajouté pour éviter l'erreur de contrainte de non-nullité
                $newParticipant->setRoles(['ROLE_USER']);

                $token = bin2hex(random_bytes(32));
                $newParticipant->setActivationToken($token);
                $newParticipant->setTokenExpiresAt(new \DateTimeImmutable('+1 hour'));

                $entityManager->persist($newParticipant);
                $entityManager->flush();

                $emailMessage = (new Email())
                    ->from('sortir@ik.me') // Ajouté l'adresse d'expéditeur
                    ->to($newParticipant->getMail())
                    ->subject('Finalisez votre inscription sur Sortir.com')
                    ->html($twig->render('emails/activation.html.twig', [
                        'participant' => $newParticipant,
                        'token' => $token,
                    ]));

                $mailer->send($emailMessage);

                $this->addFlash('success', 'Un e-mail pour finaliser votre inscription vous a été envoyé. Veuillez vérifier votre boîte de réception.');
                return $this->redirectToRoute('app_entry');
            }
        }

        return $this->render('entry/request_email.html.twig', [
            'emailForm' => $form->createView(),
        ]);
    }

    #[Route('/entry/password', name: 'app_entry_password', methods: ['GET'])]
    public function password(
        Request $request,
        ParticipantRepository $participantRepository,
        AuthenticationUtils $authenticationUtils
    ): Response
    {
        $email = $request->getSession()->get('login_email');
        if (!$email) {
            return $this->redirectToRoute('app_entry');
        }

        // get the login error if there is one
        $error = $authenticationUtils->getLastAuthenticationError();

        // The form submission is now handled by the security firewall
        // We only need to render the form

        return $this->render('entry/password_form.html.twig', [
            'email' => $email,
            'error' => $error,
        ]);
    }
}
