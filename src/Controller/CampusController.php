<?php

namespace App\Controller;

use App\Entity\Campus;
use App\Form\CampusType;
use App\Repository\CampusRepository;
use App\Repository\ParticipantRepository;
use App\Repository\SortieRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/admin/campus')]
#[IsGranted('ROLE_ADMIN')]
final class CampusController extends AbstractController
{
    #[Route(path: '/', name: 'app_campus_index', methods: ['GET'])]
    public function index(CampusRepository $campusRepository): Response
    {
        return $this->render('admin/campus/index.html.twig', [
            'campuses' => $campusRepository->findAll(),
        ]);
    }

    #[Route(path: '/new', name: 'app_campus_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $campus = new Campus();
        $form = $this->createForm(CampusType::class, $campus);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($campus);
            $entityManager->flush();

            return $this->redirectToRoute('app_campus_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('admin/campus/new.html.twig', [
            'campus' => $campus,
            'form' => $form,
        ]);
    }

    #[Route(path: '/{id}/edit', name: 'app_campus_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Campus $campus, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(CampusType::class, $campus);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_campus_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('admin/campus/edit.html.twig', [
            'campus' => $campus,
            'form' => $form,
        ]);
    }

    #[Route(path: '/{id}', name: 'app_campus_delete', methods: ['POST'])]
    public function delete(Request $request, Campus $campus, EntityManagerInterface $entityManager, SortieRepository $sortieRepository, ParticipantRepository $participantRepository): Response
    {
        if ($this->isCsrfTokenValid('delete'.$campus->getId(), $request->getPayload()->getString('_token'))) {
            $sorties = $sortieRepository->findBy(['campus' => $campus]);
            $participants = $participantRepository->findBy(['campus' => $campus]);

            if (count($sorties) > 0 || count($participants) > 0) {
                $this->addFlash('danger', 'Impossible de supprimer le campus car il est utilisé par des sorties ou des participants.');
            } else {
                $entityManager->remove($campus);
                $entityManager->flush();
                $this->addFlash('success', 'Le campus a été supprimé avec succès.');
            }
        }

        return $this->redirectToRoute('app_campus_index', [], Response::HTTP_SEE_OTHER);
    }
}
