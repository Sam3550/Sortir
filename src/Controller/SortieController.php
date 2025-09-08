<?php

namespace App\Controller;

use App\Form\Models\SortieSearch;

use App\Form\SortieFilterSearchType;
use App\Entity\Etat;
use App\Entity\Sortie;
use App\Form\AddSortieFormType;
use App\Repository\EtatRepository;
use App\Repository\SortieRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/sortie', name: 'sortie_')]
final class SortieController extends AbstractController
{

    #[Route('/', name: 'list')]
    public function list(SortieRepository $sortieRepository, Request $request): Response
    {
        $sortieSearch = new SortieSearch();
        $searchSortieForm = $this->createForm(SortieFilterSearchType::class, $sortieSearch);
        $searchSortieForm->handleRequest($request);

        if ($searchSortieForm->isSubmitted() && $searchSortieForm->isValid()) {
            $sorties = $sortieRepository->findByFilters($sortieSearch,$this->getUser() );

        }else{
            $sorties = $sortieRepository->findAll();
        }

        return $this->render('sortie/list.html.twig', [
            'sorties' => $sorties,
            'formSearchFilter' => $searchSortieForm->createView(),
        ]);
    }

    #[Route('/add', name: 'addSortie')]
    public function addSortie(
        SortieRepository $sortieRepository,
        EtatRepository $etatRepository,
        EntityManagerInterface $entityManager,
        Request $request): Response
    {
        $sortie = new Sortie();
        $addSortieForm = $this->createForm(AddSortieFormType::class, $sortie);

        $addSortieForm->handleRequest($request);

        if ($addSortieForm->isSubmitted() && $addSortieForm->isValid()) {
            //dd($addSortieForm);
            if($addSortieForm->get('enregistrer')->isClicked()) {
                $etat = $etatRepository->findOneBy(['libelle' => 'Créée']);
                $sortie->setEtat($etat);
            }
            if($addSortieForm->get('publier')->isClicked()) {
                $etat = $etatRepository->findOneBy(['libelle' => 'Ouverte']);
                $sortie->setEtat($etat);
            }
            if($addSortieForm->get('annuler')->isClicked()) {
                return $this->redirectToRoute('main_home');
            }
            $sortie->setOrganisateur($this->getUser());
            $entityManager->persist($sortie);
            $entityManager->flush();

            $this->addFlash("success", "Sortie : " . $sortie->getNom() . " ajoutée !");
            //TODO mettre bon return et vérifier avec les deux rôles
            return $this->redirectToRoute('sortie_addSortie');
        }
        return $this->render('sortie/ajouter_sortie.html.twig', [
            'addSortieForm' => $addSortieForm
        ]);
    }

    #[Route('/{id}', name: 'show', methods: ['GET'])]
    public function show(Sortie $sortie): Response
    {
        $this->denyAccessUnlessGranted('SORTIE_AFFICHER', $sortie);
        return $this->render('sortie/show.html.twig', [
            'sortie' => $sortie,
        ]);
    }

    #[Route('/{id}/edit', name: 'edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Sortie $sortie, EntityManagerInterface $entityManager): Response
    {
        $this->denyAccessUnlessGranted('SORTIE_MODIFIER', $sortie);
        $form = $this->createForm(AddSortieFormType::class, $sortie);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('sortie_list', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('sortie/edit.html.twig', [
            'sortie' => $sortie,
            'addSortieForm' => $form,
        ]);
    }

    #[Route('/{id}/annuler', name: 'annuler', methods: ['POST'])]
    public function annuler(Request $request, Sortie $sortie, EntityManagerInterface $entityManager, EtatRepository $etatRepository): Response
    {
        $this->denyAccessUnlessGranted('SORTIE_ANNULER', $sortie);

        $etat = $etatRepository->findOneBy(['libelle' => 'Annulée']);
        $sortie->setEtat($etat);
        $entityManager->flush();

        return $this->redirectToRoute('sortie_list');
    }

    #[Route('/{id}/publier', name: 'publier', methods: ['POST'])]
    public function publier(Request $request, Sortie $sortie, EntityManagerInterface $entityManager, EtatRepository $etatRepository): Response
    {
        $this->denyAccessUnlessGranted('SORTIE_PUBLIER', $sortie);

        $etat = $etatRepository->findOneBy(['libelle' => 'Ouverte']);
        $sortie->setEtat($etat);
        $entityManager->flush();

        return $this->redirectToRoute('sortie_list');
    }

    #[Route('/{id}/desister', name: 'desister', methods: ['POST'])]
    public function desister(Request $request, Sortie $sortie, EntityManagerInterface $entityManager): Response
    {
        $this->denyAccessUnlessGranted('SORTIE_DESISTER', $sortie);

        $participant = $this->getUser();
        $sortie->removeParticipant($participant);
        $entityManager->flush();

        return $this->redirectToRoute('sortie_list');
    }

    #[Route('/{id}/inscrire', name: 'inscrire', methods: ['POST'])]
    public function inscrire(Request $request, Sortie $sortie, EntityManagerInterface $entityManager): Response
    {
        $this->denyAccessUnlessGranted('SORTIE_INSCRIRE', $sortie);

        $participant = $this->getUser();
        $sortie->addParticipant($participant);
        $entityManager->flush();

        return $this->redirectToRoute('sortie_list');
    }

    #[Route('/{id}', name: 'delete', methods: ['POST'])]
    public function delete(Request $request, Sortie $sortie, EntityManagerInterface $entityManager): Response
    {
        $this->denyAccessUnlessGranted('SORTIE_SUPPRIMER', $sortie);
        if ($this->isCsrfTokenValid('delete'.$sortie->getId(), $request->request->get('_token'))) {
            $entityManager->remove($sortie);
            $entityManager->flush();
        }

        return $this->redirectToRoute('sortie_list', [], Response::HTTP_SEE_OTHER);
    }
}
