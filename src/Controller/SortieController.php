<?php

namespace App\Controller;

use App\Entity\Sortie;
use App\Form\AddSortieFormType;
use App\Repository\SortieRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
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

    #[Route('/addSortie', name: 'addSortie')]
    public function addSortie(SortieRepository $sortieRepository, EntityManagerInterface $entityManager, Request $request): Response
    {
        $sortie = new Sortie();
        $addSortieForm = $this->createForm(AddSortieFormType::class, $sortie);

        $addSortieForm->handleRequest($request);

        if ($addSortieForm->isSubmitted() && $addSortieForm->isValid()) {
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
