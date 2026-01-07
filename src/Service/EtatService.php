<?php
// src/Service/EtatService.php
namespace App\Service;

use App\Entity\Etat;
use App\Entity\Sortie;
use App\Repository\EtatRepository;
use Doctrine\ORM\EntityManagerInterface;
use App\Entity\Participant;

class EtatService
{
    private EtatRepository $etatRepository;
    private EntityManagerInterface $entityManager;

    public function __construct(
        EtatRepository $etatRepository,
        EntityManagerInterface $entityManager
    ) {
        $this->etatRepository = $etatRepository;
        $this->entityManager = $entityManager;
    }

    /**
     * Met à jour les états d'une liste de sorties
     *
     * @param Sortie[] $sorties
     */
    public function updateEtat(array $sorties): void
    {
        $now = new \DateTime();

        $etatOuverte     = $this->etatRepository->findOneBy(['libelle' => Etat::OUVERTE]);
        $etatCloturee    = $this->etatRepository->findOneBy(['libelle' => Etat::CLOTURE]);
        $etatEnCours     = $this->etatRepository->findOneBy(['libelle' => Etat::ACTENCOURS]);
        $etatTerminee    = $this->etatRepository->findOneBy(['libelle' => Etat::ACTTERMINER]);

        $modif = false; // flag pour savoir si on doit flush

        foreach ($sorties as $sortie) {
            $etat = $sortie->getEtat();

            if (!$etat) {
                continue; // sécurité : si sortie sans état, on ne touche pas
            }

            // 1. Si limite d’inscription dépassée → CLOTURÉE
            if ($etat->getLibelle() === Etat::OUVERTE && $sortie->getDateLimiteInscription() <= $now) {
                $sortie->setEtat($etatCloturee);
                $modif = true;
            }

            // 2. Si date de début passée → EN COURS
            if (in_array($etat->getLibelle(), [Etat::OUVERTE, Etat::CLOTURE])
                && $sortie->getDateHeureDebut() <= $now) {
                $sortie->setEtat($etatEnCours);
                $modif = true;
            }

            // 3. Si activité terminée → TERMINÉE
            if ($etat->getLibelle() === Etat::ACTENCOURS) {
                $dateFin = (clone $sortie->getDateHeureDebut())->modify('+' . $sortie->getDuree() . ' minutes');
                if ($dateFin <= $now) {
                    $sortie->setEtat($etatTerminee);
                    $modif = true;
                }
            }

            if ($etat->getLibelle() === Etat::ACTENCOURS) {
                $dateFin = (clone $sortie->getDateHeureDebut())->modify('+' . $sortie->getDuree() . ' minutes');
                if ($dateFin <= $now) {
                    $sortie->setEtat($etatTerminee);
                    $modif = true;
                }
            }

        }

        if ($modif) {
            $this->entityManager->flush();
        }
    }

}
