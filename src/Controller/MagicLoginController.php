<?php

namespace App\Controller;

use App\Entity\Participant;
use App\Form\EmailCheckType;
use App\Form\PasswordLoginType;
use App\Form\RegistrationCompleteType;
use App\Repository\ParticipantRepository;
use App\Security\EmailVerifier;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mime\Address;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;
use SymfonyCasts\Bundle\VerifyEmail\Exception\VerifyEmailExceptionInterface;

class MagicLoginController extends AbstractController
{
    private EmailVerifier $emailVerifier;

    public function __construct(EmailVerifier $emailVerifier)
    {
        $this->emailVerifier = $emailVerifier;
    }

    #[Route('/', name: 'app_magic_login')]
    public function index(Request $request, ParticipantRepository $participantRepository): Response
    {
        if ($this->getUser()) {
            return $this->redirectToRoute('app_profile');
        }

        $form = $this->createForm(EmailCheckType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();
            $email = $data['email'];

            $participant = $participantRepository->findOneBy(['mail' => $email]);

            if ($participant) {
                return $this->redirectToRoute('app_magic_login_password', ['email' => $email]);
            } else {
                // User does not exist, send magic link for registration.
                $user = new Participant();
                $user->setMail($email);

                $this->emailVerifier->sendEmailConfirmation('app_magic_register_complete', $user,
                    (new TemplatedEmail())
                        ->from(new Address('contact@beaj.fr', 'Sortir.com'))
                        ->to($user->getMail())
                        ->subject('Finalisez votre inscription !')
                        ->htmlTemplate('registration/confirmation_email.html.twig')
                );

                return $this->render('magic_login/check_email.html.twig', ['email' => $email]);
            }
        }

        return $this->render('magic_login/index.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/login-password', name: 'app_magic_login_password')]
    public function loginPassword(Request $request, ParticipantRepository $participantRepository, UserPasswordHasherInterface $passwordHasher, Security $security): Response
    {
        $email = $request->query->get('email');
        $participant = $participantRepository->findOneBy(['mail' => $email]);

        if (!$participant) {
            $this->addFlash('error', 'Utilisateur non trouvé.');
            return $this->redirectToRoute('app_magic_login');
        }

        $form = $this->createForm(PasswordLoginType::class, ['email' => $email]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();
            $password = $data['password'];

            if ($passwordHasher->isPasswordValid($participant, $password)) {
                return $security->login($participant, 'form_login', 'main');
            }

            $this->addFlash('error', 'Mot de passe incorrect.');
        }

        return $this->render('magic_login/password_login.html.twig', [
            'form' => $form->createView(),
            'email' => $email,
        ]);
    }

    #[Route('/register-complete', name: 'app_magic_register_complete')]
    public function registerComplete(Request $request, UserPasswordHasherInterface $passwordHasher, EntityManagerInterface $entityManager, Security $security): Response
    {
        $user = new Participant();

        try {
            $this->emailVerifier->handleEmailConfirmation($request, $user);
        } catch (VerifyEmailExceptionInterface $exception) {
            $this->addFlash('verify_email_error', $exception->getReason());
            return $this->redirectToRoute('app_magic_login');
        }

        $form = $this->createForm(RegistrationCompleteType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $plainPassword = $form->get('plainPassword')->getData();
            $user->setPassword($passwordHasher->hashPassword($user, $plainPassword));
            $user->setActif(true);

            $entityManager->persist($user);
            $entityManager->flush();

            return $security->login($user, 'form_login', 'main');
        }

        return $this->render('magic_login/register_complete.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/logout', name: 'app_logout')]
    public function logout(): void
    {
        throw new \Exception('This should never be reached!');
    }
}
