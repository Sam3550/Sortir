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

#[Route('/', name: 'sortie_')]
final class SortieController extends AbstractController
{
    #[Route('/list', name: 'list')]
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

        dump($sorties);
        return $this->render('sortie/list.html.twig', [
            'sorties' => $sorties,
            'formSearchFilter' => $searchSortieForm->createView(),
        ]);
    }

    #[Route('/addSortie', name: 'addSortie')]
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
            //TODO décommenter le setOrganiateur quand connexion/deco okay
            //$sortie->setOrganisateur($this->getUser());
            $entityManager->persist($sortie);
            $entityManager->flush();

            $this->addFlash("success", "Sortie : " . $sortie->getNom() . " ajoutée !");
            //TODO mettre bon return
            return $this->redirectToRoute('sortie_addSortie');
        }
        return $this->render('sortie/ajouter_sortie.html.twig', [
            'addSortieForm' => $addSortieForm
        ]);
    }
}
