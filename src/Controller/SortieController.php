<?php

namespace App\Controller;

use App\Entity\Sortie;
use App\Form\AddSortieFormType;
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

    #[Route('/list', name: 'list')]
    public function list(SortieRepository $sortieRepository, Request $request, EtatService $etatService): Response
    {

        $sortieSearch = new SortieSearch();
        $searchSortieForm = $this->createForm(SortieFilterSearchType::class, $sortieSearch);
        $searchSortieForm->handleRequest($request);

        if ($searchSortieForm->isSubmitted() && $searchSortieForm->isValid()) {
            $sorties = $sortieRepository->findByFilters($sortieSearch, $this->getUser());


        } else {
            $sorties = $sortieRepository->findAll();

        }

        //récupère les filtres pour mettre a jour les états
        $etatService->updateEtat($sorties);
        //Filtre la sortie si l'etat est creee mais que je ne suis pas l'organisateur alors je ne l'affiche pas
        $user = $this->getUser();
                $sorties = array_filter($sorties, function (Sortie $sortie) use ($user) {
            $etat = $sortie->getEtat();
            return !($etat && $etat->getLibelle() === \App\Entity\Etat::CREEE && $sortie->getOrganisateur() !== $user);
        });

        dump($sorties);
        return $this->render('sortie/list.html.twig', [
            'sorties' => $sorties,
            'formSearchFilter' => $searchSortieForm->createView(),
        ]);
    }

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


        // persist et flush...

        return $this->redirectToRoute('sortie_list', ['id' => $id]);

    }

    #[Route('/addSortie', name: 'addSortie')]
    public function addSortie(
        SortieRepository       $sortieRepository,
        EtatRepository         $etatRepository,
        EntityManagerInterface $entityManager,
        Request                $request): Response
    {
        $sortie = new Sortie();
        $addSortieForm = $this->createForm(AddSortieFormType::class, $sortie);

        $addSortieForm->handleRequest($request);

        if ($addSortieForm->isSubmitted() && $addSortieForm->isValid()) {

            if($addSortieForm->get('enregistrer')->isClicked()) {
                $etat = $etatRepository->findOneBy(['libelle' => 'Créée']);
                $sortie->setEtat($etat);
            }
            if($addSortieForm->get('publier')->isClicked()) {
                $etat = $etatRepository->findOneBy(['libelle' => 'Ouverte']);
                $sortie->setEtat($etat);
            }
            if($addSortieForm->get('annuler')->isClicked()) {
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

    #[Route('/detail/{id}', name: 'detail', requirements: ['id' => '\d+'])]
    public function detailSortie(int $id, SortieRepository $sortieRepository): Response{
        $sortie = $sortieRepository->find($id);

        if (!$sortie) {
            throw $this->createNotFoundException("Oops ! Cette sortie n'existe pas !");
        }

        return $this->render('sortie/detailSortie.html.twig', [
            'sortie' => $sortie
        ]);
    }

    #[Route('/update/{id}', name: 'update', requirements: ['id' => '\d+'])]
    public function update(
        int $id,
        SortieRepository $sortieRepository,
        Request $request,
        EntityManagerInterface $entityManager): Response
    {
        $sortie = $sortieRepository->find($id);

        if (!$sortie) {
            throw $this->createNotFoundException("Cette sortie n'existe pas !");
        }

        $sortieMAJForm = $this->createForm(AddSortieFormType::class, $sortie);
        $sortieMAJForm->handleRequest($request);

        //Attention : on annule une sortie mais on ne la supprime pas de la BDD, c'est un update d'état
        // + ajout du motif de l'annulation !
        if($sortieMAJForm->isSubmitted() && $sortieMAJForm->isValid()) {

            if($sortieMAJForm->get('supprimer')->isClicked()) {
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

    #[Route('/delete/{id}', name: 'delete', requirements: ['id' => '\d+'])]
    public function delete(
        int $id,
        SortieRepository $sortieRepository,
        EtatRepository $etatRepository,
        Request $request,
        EntityManagerInterface $entityManager): Response
    {
        $sortie = $sortieRepository->find($id);

        if (!$sortie) {
            throw $this->createNotFoundException("Cette sortie n'existe pas !");
        }

        $sortieAnnulerForm = $this->createForm(DeleteSortieFormType::class, $sortie);
        $sortieAnnulerForm->handleRequest($request);

        //Attention : on annule une sortie mais on ne la supprime pas de la BDD, c'est un update d'état
        // + ajout du motif de l'annulation !
        if($sortieAnnulerForm->isSubmitted() && $sortieAnnulerForm->isValid()) {

            if($sortieAnnulerForm->get('annuler')->isClicked()) {
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
