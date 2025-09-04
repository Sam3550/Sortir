<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

use Symfony\Component\HttpFoundation\Request;

final class HomeController extends AbstractController
{
    #[Route('/home', name: 'home')]
    public function home(Request $request): Response
    {
        if ($request->isMethod('POST')) {
            $email = $request->request->get('email');
            // TODO: Store the email address
            // For now, we'll just add a flash message
            $this->addFlash('success', 'Merci ! Nous vous tiendrons au courant.');
            return $this->redirectToRoute('home');
        }

        return $this->render('home/index.html.twig', [
            'controller_name' => 'HomeController',
        ]);
    }

}
