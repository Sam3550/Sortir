<?php
// src/Service/EtatService.php
namespace App\Service;

use App\Entity\Sortie;

class EtatService
{
    public function updateEtat(Sortie $sortie)
    {
        $aujourdhui = new \DateTime();
    //mettre une logique pour la mise a jour automatique des etats et non pour une action utilisateur
    }
}
