<?php

namespace App\Controller;

use App\Entity\Etat;
use App\Entity\Sortie;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

final class InscritController extends AbstractController
{
    #[Route('/inscrit/{id}', name: 'inscrit')]
    #[IsGranted('SORTIE_INSCRIRE', 'sortie')]
    public function inscrit(Sortie $sortie, EntityManagerInterface $entityManager): Response
    {
        /** @var \App\Entity\Participant|null $userConnecter */
        $userConnecter = $this->getUser();

        if (!$userConnecter) {
            $this->addFlash('error', 'Il faut que vous soyez connecté pour vous inscrire.');
            return $this->redirectToRoute('app_magic_login');
        }

        $sortie->addParticipant($userConnecter);

        // Si le nombre max d'inscriptions est atteint, passer l'état à "Clôturée"
        if (count($sortie->getParticipants()) >= $sortie->getNbInscriptionMax()) {
            $etatCloture = $entityManager->getRepository(Etat::class)->findOneBy(['libelle' => Etat::CLOTURE]);
            if ($etatCloture) {
                $sortie->setEtat($etatCloture);
            }
        }

        $entityManager->persist($sortie);
        $entityManager->flush();

        $this->addFlash('success', 'Vous êtes bien inscrit à la sortie, hehe.');

        return $this->redirectToRoute('sortie_list');
    }

    #[Route('/desister/{id}', name: 'desister')]
    #[IsGranted('SORTIE_DESISTER', 'sortie')]
    public function desister(Sortie $sortie, EntityManagerInterface $entityManager): Response
    {
        /** @var \App\Entity\Participant|null $userConnecter */
        $userConnecter = $this->getUser();

        if (!$userConnecter) {
            $this->addFlash('error', 'Il faut que vous soyez connecté pour vous désinscrire.');
            return $this->redirectToRoute('app_magic_login');
        }

        $sortie->removeParticipant($userConnecter);

        // Si la sortie était clôturée et qu'une place se libère, la réouvrir
        if ($sortie->getEtat()->getLibelle() === Etat::CLOTURE) {
            $etatOuverte = $entityManager->getRepository(Etat::class)->findOneBy(['libelle' => Etat::OUVERTE]);
            if ($etatOuverte) {
                $sortie->setEtat($etatOuverte);
            }
        }

        $entityManager->persist($sortie);
        $entityManager->flush();

        $this->addFlash('warning', 'Vous vous êtes bien désinscrit de la sortie.');

        return $this->redirectToRoute('sortie_list');
    }
}
