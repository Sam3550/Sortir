<?php

namespace App\Controller;

use App\Repository\SortieRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/', name: 'sortie_')]
final class SortieController extends AbstractController
{
    #[Route('/list/{id}', name: 'list')]
    public function list(SortieRepository $sortieRepository, int $idList): Response
    {
        $sortie = $sortieRepository->find($idList);
        dump($sortie);

        return $this->render('sortie/afficher.html.twig',[
            'sortie' => $sortie,
        ]);
    }
}
