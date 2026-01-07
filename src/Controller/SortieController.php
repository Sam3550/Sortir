<?php

namespace App\Controller;

use App\Entity\Lieu;
use App\Entity\Sortie;
use App\Entity\Ville;
use App\Form\AddSortieFormType;
use App\Form\Models\SortieSearch;
use App\Form\SortieFilterSearchType;
use App\Repository\EtatRepository;
use App\Repository\SortieRepository;
use App\Repository\VilleRepository;
use App\Service\EtatService;
use App\Service\GeocodingService;
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
            $sorties = $sortieRepository->findByFilters(new SortieSearch(), $this->getUser());
        }

        //récupère les filtres pour mettre a jour les états
        $etatService->updateEtat($sorties);
        //Filtre la sortie si l'etat est creee mais que je ne suis pas l'organisateur alors je ne l'affiche pas
        $user = $this->getUser();
        $sorties = array_filter($sorties, function (Sortie $sortie) use ($user) {
            return !($sortie->getEtat()->getLibelle() === \App\Entity\Etat::CREEE && $sortie->getOrganisateur() !== $user);
        });

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
        VilleRepository        $villeRepository,
        EntityManagerInterface $entityManager,
        Request                $request): Response
    {
        $sortie = new Sortie();
        $addSortieForm = $this->createForm(AddSortieFormType::class, $sortie);

        $addSortieForm->handleRequest($request);

        if ($addSortieForm->isSubmitted() && $addSortieForm->isValid()) {
            
            // Gestion du bouton Annuler
            if ($addSortieForm->get('annuler')->isClicked()) {
                return $this->redirectToRoute('sortie_list');
            }

            // ========================================
            // GESTION DU LIEU
            // ========================================
            $lieuType = $addSortieForm->get('lieuType')->getData();
            $lieu = null;

            switch ($lieuType) {
                case 'existant':
                    // Lieu existant sélectionné dans la liste
                    $lieu = $addSortieForm->get('lieu')->getData();
                    if (!$lieu) {
                        $this->addFlash('error', 'Veuillez sélectionner un lieu existant.');
                        return $this->render('sortie/ajouter_sortie.html.twig', [
                            'addSortieForm' => $addSortieForm
                        ]);
                    }
                    break;

                case 'adresse':
                    // Création d'un nouveau lieu à partir d'une adresse
                    $nomLieu = $addSortieForm->get('nouveauLieuNom')->getData();
                    $numero = $addSortieForm->get('nouveauLieuNumero')->getData();
                    $rue = $addSortieForm->get('nouveauLieuRue')->getData();
                    $codePostal = $addSortieForm->get('nouveauLieuCodePostal')->getData();
                    $villeNom = $addSortieForm->get('nouveauLieuVille')->getData();

                    if (!$nomLieu || !$rue || !$codePostal || !$villeNom) {
                        $this->addFlash('error', 'Veuillez remplir tous les champs de l\'adresse.');
                        return $this->render('sortie/ajouter_sortie.html.twig', [
                            'addSortieForm' => $addSortieForm
                        ]);
                    }

                    // Chercher ou créer la ville
                    $ville = $villeRepository->findOneBy(['nom' => $villeNom]);
                    if (!$ville) {
                        $ville = new Ville();
                        $ville->setNom($villeNom);
                        $ville->setCodePostal($codePostal);
                        $entityManager->persist($ville);
                    }

                    // Construire l'adresse complète pour le géocodage
                    $adresseComplete = trim($numero . ' ' . $rue . ', ' . $codePostal . ' ' . $villeNom . ', France');
                    
                    // Géocoder l'adresse pour obtenir les coordonnées
                    $coordinates = $this->geocodeAddress($adresseComplete);

                    // Créer le nouveau lieu
                    $lieu = new Lieu();
                    $lieu->setNom($nomLieu);
                    $lieu->setRue(trim($numero . ' ' . $rue));
                    $lieu->setLatitude($coordinates['lat']);
                    $lieu->setLongitude($coordinates['lng']);
                    $lieu->setVille($ville);
                    $entityManager->persist($lieu);
                    break;

                case 'gps':
                    // Création d'un nouveau lieu à partir de coordonnées GPS
                    $nomLieu = $addSortieForm->get('gpsNom')->getData();
                    $latitude = $addSortieForm->get('gpsLatitude')->getData();
                    $longitude = $addSortieForm->get('gpsLongitude')->getData();
                    $ville = $addSortieForm->get('gpsVille')->getData();

                    if (!$nomLieu || $latitude === null || $longitude === null || !$ville) {
                        $this->addFlash('error', 'Veuillez remplir tous les champs GPS (nom, latitude, longitude et ville).');
                        return $this->render('sortie/ajouter_sortie.html.twig', [
                            'addSortieForm' => $addSortieForm
                        ]);
                    }

                    // Créer le nouveau lieu
                    $lieu = new Lieu();
                    $lieu->setNom($nomLieu);
                    $lieu->setRue('Coordonnées GPS');
                    $lieu->setLatitude((float) $latitude);
                    $lieu->setLongitude((float) $longitude);
                    $lieu->setVille($ville);
                    $entityManager->persist($lieu);
                    break;
            }

            // Assigner le lieu à la sortie
            $sortie->setLieu($lieu);

            // ========================================
            // GESTION DE L'ÉTAT
            // ========================================
            if ($addSortieForm->get('enregistrer')->isClicked()) {
                $etat = $etatRepository->findOneBy(['libelle' => 'Créée']);
                $sortie->setEtat($etat);
            }
            if ($addSortieForm->get('publier')->isClicked()) {
                $etat = $etatRepository->findOneBy(['libelle' => 'Ouverte']);
                $sortie->setEtat($etat);
            }

            // Assigner l'organisateur (utilisateur connecté)
            $sortie->setOrganisateur($this->getUser());
            
            // Assigner le campus de l'organisateur
            $user = $this->getUser();
            if ($user && method_exists($user, 'getCampus')) {
                $sortie->setCampus($user->getCampus());
            }

            $entityManager->persist($sortie);
            $entityManager->flush();

            $this->addFlash("success", "Sortie « " . $sortie->getNom() . " » ajoutée avec succès !");
            return $this->redirectToRoute('sortie_list');
        }
        
        return $this->render('sortie/ajouter_sortie.html.twig', [
            'addSortieForm' => $addSortieForm
        ]);
    }

    /**
     * Géocode une adresse en utilisant l'API Nominatim (OpenStreetMap)
     * Retourne les coordonnées lat/lng ou des valeurs par défaut si échec
     */
    private function geocodeAddress(string $address): array
    {
        $defaultCoords = ['lat' => 47.2184, 'lng' => -1.5536]; // Nantes par défaut

        try {
            $url = 'https://nominatim.openstreetmap.org/search?' . http_build_query([
                'q' => $address,
                'format' => 'json',
                'limit' => 1
            ]);

            $context = stream_context_create([
                'http' => [
                    'header' => 'User-Agent: Sortir.com/1.0'
                ]
            ]);

            $response = @file_get_contents($url, false, $context);
            
            if ($response) {
                $data = json_decode($response, true);
                if (!empty($data) && isset($data[0]['lat'], $data[0]['lon'])) {
                    return [
                        'lat' => (float) $data[0]['lat'],
                        'lng' => (float) $data[0]['lon']
                    ];
                }
            }
        } catch (\Exception $e) {
            // En cas d'erreur, on retourne les coordonnées par défaut
        }

        return $defaultCoords;
    }

    #[Route('/detailSortie/{id}', name: 'detailSortie', requirements: ['id' => '\d+'])]
    public function detailSortie(int $id, SortieRepository $sortieRepository): Response
    {
        $sortie = $sortieRepository->find($id);

        if (!$sortie) {
            throw $this->createNotFoundException("Oops ! Cette sortie n'existe pas !");
        }

        return $this->render('sortie/detailSortie.html.twig', [
            'sortie' => $sortie
        ]);
    }
}
