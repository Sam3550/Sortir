<?php

namespace App\Controller;

use App\Entity\Participant;
use App\Form\ParticipantType;
use App\Repository\ParticipantRepository;
use App\Repository\SortieRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\String\Slugger\SluggerInterface;

#[Route('/admin/users')]
#[IsGranted('ROLE_ADMIN')]
final class AdminParticipantController extends AbstractController
{
    #[Route(path: '/', name: 'app_admin_participant_index', methods: ['GET'])]
    public function index(ParticipantRepository $participantRepository): Response
    {
        return $this->render('admin/participant/index.html.twig', [
            'participants' => $participantRepository->findAll(),
        ]);
    }

    #[Route(path: '/{id}', name: 'app_admin_participant_show', methods: ['GET'])]
    public function show(Participant $participant): Response
    {
        return $this->render('admin/participant/show.html.twig', [
            'participant' => $participant,
        ]);
    }

    #[Route(path: '/{id}/edit', name: 'app_admin_participant_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Participant $participant, EntityManagerInterface $entityManager, SluggerInterface $slugger): Response
    {
        $form = $this->createForm(ParticipantType::class, $participant);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            $this->addFlash('success', 'Utilisateur mis à jour avec succès.');

            return $this->redirectToRoute('app_admin_participant_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('admin/participant/edit.html.twig', [
            'participant' => $participant,
            'form' => $form,
        ]);
    }

    #[Route(path: '/{id}', name: 'app_admin_participant_delete', methods: ['POST'])]
    public function delete(Request $request, Participant $participant, EntityManagerInterface $entityManager, SortieRepository $sortieRepository): Response
    {
        if ($this->isCsrfTokenValid('delete'.$participant->getId(), $request->getPayload()->getString('_token'))) {
            // Check if the participant is an organizer of any sorties
            $organizedSorties = $sortieRepository->findBy(['organisateur' => $participant]);

            if (!empty($organizedSorties)) {
                // Set organizer to null for organized sorties
                foreach ($organizedSorties as $sortie) {
                    $sortie->setOrganisateur(null);
                }
                $entityManager->flush();
            }

            // Remove participant from any sorties they are registered for
            foreach ($participant->getSorties() as $sortie) {
                $sortie->removeParticipant($participant);
            }
            $entityManager->flush();

            $entityManager->remove($participant);
            $entityManager->flush();

            $this->addFlash('success', 'Utilisateur supprimé avec succès.');
        }

        return $this->redirectToRoute('app_admin_participant_index', [], Response::HTTP_SEE_OTHER);
    }
}
