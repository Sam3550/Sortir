<?php

namespace App\Controller;

use App\Entity\Participant;
use App\Entity\Sortie;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class InscritController extends AbstractController
{

    #[Route('/inscrit', name: 'inscrit')]
    public function inscrit(Sortie $sortie, EntityManagerInterface $entityManager): Response
    {
        $userConnecter = $this->getUser();

        if (!$userConnecter) {
            $this->addFlash('error', 'Il faut que vous soyez connecté pour vous inscrire.');
            return $this->redirectToRoute('app_login');
        }else{


            $sortie->addParticipant($userConnecter);
            $entityManager->persist($sortie);
            $entityManager->flush();

        }
        return $this->redirectToRoute('sortie_list');
    }
}
