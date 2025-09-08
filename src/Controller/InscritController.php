<?php

namespace App\Controller;

use App\Entity\Etat;
use App\Entity\Sortie;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class InscritController extends AbstractController
{
    #[Route('/inscrit/{id}', name: 'inscrit')]
    #[IsGranted('inscrit', 'sortie')]
    public function inscrit(Sortie $sortie, EntityManagerInterface $entityManager): Response
    {
        /** @var \App\Entity\Participant|null $userConnecter */
        $userConnecter = $this->getUser();

        if (!$userConnecter) {
            $this->addFlash('error', 'Il faut que vous soyez connecté pour vous inscrire.');
            return $this->redirectToRoute('app_magic_login');
        }

        $sortie->addParticipant($userConnecter);

        if (count($sortie->getParticipants()) == $sortie->getNbInscriptionMax()){


        }

        $sortie->getEtat()->setLibelle(Etat::OUVERTE);




        $entityManager->persist($sortie);
        $entityManager->flush();

        $this->addFlash('success', 'Vous êtes bien inscrit à la sortie, hehe.');

        return $this->redirectToRoute('sortie_list');
    }

    #[Route('/desister/{id}', name: 'desister')]
    public function desister(Sortie $sortie, EntityManagerInterface $entityManager): Response
    {
        /** @var \App\Entity\Participant|null $userConnecter */
        $userConnecter = $this->getUser();

        if (!$userConnecter) {
            $this->addFlash('error', 'Il faut que vous soyez connecté pour vous inscrire.');
            return $this->redirectToRoute('app_magic_login');
        }

        $sortie->removeParticipant($userConnecter);
        $sortie->getEtat()->setLibelle(Etat::OUVERTE);
        $entityManager->persist($sortie);
        $entityManager->flush();

        $this->addFlash('error', 'Vous vous étes bien désinscrit, c\'est dommage');

        return $this->redirectToRoute('sortie_list');
    }
}
