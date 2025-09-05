<?php

namespace App\Controller;

use App\Entity\Sortie;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class InscritController extends AbstractController
{
    #[Route('/inscrit/{id}', name: 'inscrit')]
    public function inscrit(Sortie $sortie, EntityManagerInterface $entityManager): Response
    {
        /** @var \App\Entity\Participant|null $userConnecter */
        $userConnecter = $this->getUser();

        if (!$userConnecter) {
            $this->addFlash('error', 'Il faut que vous soyez connecté pour vous inscrire.');
            return $this->redirectToRoute('app_magic_login');
        }

        $sortie->addParticipant($userConnecter);
        $entityManager->persist($sortie);
        $entityManager->flush();

        $this->addFlash('success', 'Vous êtes bien inscrit à la sortie.');

        return $this->redirectToRoute('sortie_list');
    }
}
