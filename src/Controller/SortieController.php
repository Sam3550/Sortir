<?php

namespace App\Controller;

use App\Entity\Sortie;
use App\Form\AddSortieFormType;
use App\Form\DeleteSortieFormType;
use App\Form\Models\SortieSearch;
use App\Form\SortieFilterSearchType;
use App\Repository\EtatRepository;
use App\Repository\SortieRepository;
use App\Service\EtatService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/', name: 'sortie_')]
final class SortieController extends AbstractController
{
    /**
     * Méthode pour afficher la liste des sorties
     * Cette méthode récupère la liste des sorties en fonction des filtres de recherche
     * et met à jour l'état des sorties.
     * @param SortieRepository $sortieRepository
     * @param Request $request
     * @param EtatService $etatService
     * @return Response
     */
    #[Route('/list', name: 'list')]
    public function list(SortieRepository $sortieRepository, Request $request, EtatService $etatService): Response
    {
        // Création du formulaire de recherche
        $sortieSearch = new SortieSearch();
        $searchSortieForm = $this->createForm(SortieFilterSearchType::class, $sortieSearch);
        $searchSortieForm->handleRequest($request);

        // Récupération des sorties en fonction des filtres
        if ($searchSortieForm->isSubmitted() && $searchSortieForm->isValid()) {
            $sorties = $sortieRepository->findByFilters($sortieSearch, $this->getUser());
        } else {
            $sorties = $sortieRepository->findAll();
        }

        // Mise à jour de l'état des sorties
        $etatService->updateEtat($sorties);

        // Filtre les sorties pour ne pas afficher les sorties "Créée" par d'autres utilisateurs
        $user = $this->getUser();
        $sorties = array_filter($sorties, function (Sortie $sortie) use ($user) {
            $etat = $sortie->getEtat();
            return !($etat && $etat->getLibelle() === \App\Entity\Etat::CREEE && $sortie->getOrganisateur() !== $user);
        });

        return $this->render('sortie/list.html.twig', [
            'sorties' => $sorties,
            'formSearchFilter' => $searchSortieForm->createView(),
        ]);
    }

    /**
     * Méthode pour publier une sortie
     * Cette méthode change l'état d'une sortie de "Créée" à "Ouverte".
     * @param int $id
     * @param SortieRepository $sortieRepository
     * @param EtatRepository $etatRepository
     * @param EntityManagerInterface $entityManager
     * @return Response
     */
    #[Route('/sortie/{id}/publier', name: 'publier')]
    public function publierSortie(int $id, SortieRepository $sortieRepository, EtatRepository $etatRepository, EntityManagerInterface $entityManager): Response
    {
        $sortie = $sortieRepository->find($id);
        if ($sortie->getEtat()->getLibelle() === \App\Entity\Etat::CREEE) {
            $etat = $etatRepository->findOneBy(['libelle' => 'Ouverte']);
            $sortie->setEtat($etat);
            $entityManager->persist($sortie);
            $entityManager->flush();
        }

        return $this->redirectToRoute('sortie_list', ['id' => $id]);
    }

    /**
     * Méthode pour ajouter une sortie
     * Cette méthode gère le formulaire d'ajout de sortie et la création de la sortie en base de données.
     * @param EtatRepository $etatRepository
     * @param EntityManagerInterface $entityManager
     * @param Request $request
     * @return Response
     */
    #[Route('/addSortie', name: 'addSortie')]
    public function addSortie(
        EtatRepository         $etatRepository,
        EntityManagerInterface $entityManager,
        Request                $request): Response
    {
        $sortie = new Sortie();
        $addSortieForm = $this->createForm(AddSortieFormType::class, $sortie);
        $addSortieForm->handleRequest($request);

        if ($addSortieForm->isSubmitted() && $addSortieForm->isValid()) {
            // Gestion des différents boutons du formulaire
            if ($addSortieForm->get('enregistrer')->isClicked()) {
                $etat = $etatRepository->findOneBy(['libelle' => 'Créée']);
                $sortie->setEtat($etat);
            }
            if ($addSortieForm->get('publier')->isClicked()) {
                $etat = $etatRepository->findOneBy(['libelle' => 'Ouverte']);
                $sortie->setEtat($etat);
            }
            if ($addSortieForm->get('annuler')->isClicked()) {
                return $this->redirectToRoute('sortie_list');
            }

            $sortie->setOrganisateur($this->getUser());
            $entityManager->persist($sortie);
            $entityManager->flush();

            $this->addFlash("success", "Sortie : " . $sortie->getNom() . " ajoutée !");

            return $this->redirectToRoute('sortie_detail', ['id' => $sortie->getId()]);
        }

        return $this->render('sortie/ajouter_sortie.html.twig', [
            'addSortieForm' => $addSortieForm
        ]);
    }

    /**
     * Méthode pour afficher les détails d'une sortie
     * @param int $id
     * @param SortieRepository $sortieRepository
     * @return Response
     */
    #[Route('/detail/{id}', name: 'detail', requirements: ['id' => '\d+'])]
    public function detailSortie(int $id, SortieRepository $sortieRepository): Response
    {
        $sortie = $sortieRepository->find($id);

        if (!$sortie) {
            throw $this->createNotFoundException("Oops ! Cette sortie n'existe pas !");
        }

        dump($sortie);

        return $this->render('sortie/detailSortie.html.twig', [
            'sortie' => $sortie
        ]);
    }

    /**
     * Méthode pour mettre à jour une sortie
     * @param int $id
     * @param SortieRepository $sortieRepository
     * @param Request $request
     * @param EntityManagerInterface $entityManager
     * @return Response
     */
    #[Route('/update/{id}', name: 'update', requirements: ['id' => '\d+'])]
    public function update(
        int                    $id,
        SortieRepository       $sortieRepository,
        Request                $request,
        EntityManagerInterface $entityManager): Response
    {
        $sortie = $sortieRepository->find($id);

        if (!$sortie) {
            throw $this->createNotFoundException("Cette sortie n'existe pas !");
        }

        $sortieMAJForm = $this->createForm(AddSortieFormType::class, $sortie);
        $sortieMAJForm->handleRequest($request);

        if ($sortieMAJForm->isSubmitted() && $sortieMAJForm->isValid()) {
            // Gestion du bouton de suppression
            if ($sortieMAJForm->get('supprimer')->isClicked()) {
                $entityManager->remove($sortie);
                $entityManager->flush();
                return $this->redirectToRoute('sortie_list');
            }

            $entityManager->persist($sortie);
            $entityManager->flush();

            return $this->redirectToRoute('sortie_detail', ['id' => $sortie->getId()]);
        }

        return $this->render('sortie/update_sortie.html.twig', [
            'sortie' => $sortie,
            'sortieMAJForm' => $sortieMAJForm
        ]);
    }

    /**
     * Méthode pour annuler une sortie
     * Attention : on annule une sortie mais on ne la supprime pas de la BDD, c'est un update d'état
     * + ajout du motif de l'annulation !
     * @param int $id
     * @param SortieRepository $sortieRepository
     * @param EtatRepository $etatRepository
     * @param Request $request
     * @param EntityManagerInterface $entityManager
     * @return Response
     */
    #[Route('/delete/{id}', name: 'delete', requirements: ['id' => '\d+'])]
    public function delete(
        int                    $id,
        SortieRepository       $sortieRepository,
        EtatRepository         $etatRepository,
        Request                $request,
        EntityManagerInterface $entityManager): Response
    {
        $sortie = $sortieRepository->find($id);

        if (!$sortie) {
            throw $this->createNotFoundException("Cette sortie n'existe pas !");
        }

        $sortieAnnulerForm = $this->createForm(DeleteSortieFormType::class, $sortie);
        $sortieAnnulerForm->handleRequest($request);

        if ($sortieAnnulerForm->isSubmitted() && $sortieAnnulerForm->isValid()) {
            if ($sortieAnnulerForm->get('annuler')->isClicked()) {
                $etat = $etatRepository->findOneBy(['libelle' => 'Annulée']);
                $sortie->setEtat($etat);
            }

            $entityManager->persist($sortie);
            $entityManager->flush();

            return $this->redirectToRoute('sortie_detailSortie', ['id' => $sortie->getId()]);
        }

        return $this->render('sortie/annuler_sortie.html.twig', [
            'sortie' => $sortie,
            'sortieAnnulerForm' => $sortieAnnulerForm
        ]);
    }
}
