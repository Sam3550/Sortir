<?php

namespace App\Controller;

use App\Form\Models\SortieSearch;

use App\Form\SortieFilterSearchType;
use App\Repository\SortieRepository;
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
}
