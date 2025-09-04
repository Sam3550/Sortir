<?php

namespace App\Security\Voter;

use App\Entity\Etat;
use App\Entity\Sortie;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\User\UserInterface;

final class SortieVoter extends Voter
{
    public const AFFICHER = 'SORTIE_AFFICHER';
    public const DESISTER = 'SORTIE_DESISTER';
    public const INSCRIRE = 'SORTIE_INSCRIRE';
    public const MODIFIER = 'SORTIE_MODIFIER';
    public const PUBLIER = 'SORTIE_PUBLIER';

    public const ANNULER = 'SORTIE_ANNULER';

    protected function supports(string $attribute, mixed $subject): bool
    {
        // replace with your own logic
        // https://symfony.com/doc/current/security/voters.html
        return in_array($attribute, [self::AFFICHER, self::DESISTER, self::INSCRIRE, self::MODIFIER, self::PUBLIER, self::ANNULER])
            && $subject instanceof \App\Entity\Sortie;
    }

    protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token): bool
    {
        $participant = $token->getUser();

        // if the user is anonymous, do not grant access
        if (!$participant instanceof UserInterface) {
            return false;
        }

        /**
         * @var Sortie $subject
         */


        // ... (check conditions and return true to grant permission) ...
        switch ($attribute) {
            case self::AFFICHER:
                return $this->afficher($subject, $participant);

            case self::DESISTER:
                return $this->desister($subject, $participant);


            case self::INSCRIRE:
                return $this->inscrire($subject, $participant);


            case self::MODIFIER:
                return $this->modifier($subject, $participant);


            case self::PUBLIER:
                return $this->publier($subject, $participant);

            case self::ANNULER:
                return $this->annuler($subject, $participant);
        }

        return false;
    }

    private function afficher(Sortie $sortie, UserInterface $participant): bool
    {
        if ($sortie->getEtat()->getLibelle() !== Etat::CREEE || $participant === $sortie->getOrganisateur()) {
            return true;
        }
        return false;

    }


    private function desister(Sortie $sortie, UserInterface $participant): bool
    {
        if ($sortie->getParticipants()->contains($participant)) {
            return true; // peut se désister
        } else {
            return false; // pas participant → pas de désistement possible
        }
    }

    private function inscrire(Sortie $sortie, UserInterface $participant): bool
    {
        if (!$sortie->getParticipants()->contains($participant) && $sortie->getEtat()->getLibelle() !== Etat::CREEE) {
            return true; // peut s'inscrire
        } else {
            return false; // déjà participant → interdit
        }
    }

    private function modifier(Sortie $sortie, UserInterface $participant): bool
    {
        if ($sortie->getEtat()->getLibelle() === Etat::CREEE and $participant === $sortie->getOrganisateur()) {
            return true; // peut modifer
        } else {
            return false; // pas possible de modifier
        }
    }

    private function publier(Sortie $sortie, UserInterface $participant): bool
    {
        if ($sortie->getEtat()->getLibelle() === Etat::CREEE and $participant === $sortie->getOrganisateur()) {
            return true; // peut publier
        } else {
            return false; // peut pas publier
        }
    }

    private function annuler(Sortie $sortie, UserInterface $participant): bool
    {
        if ($sortie->getEtat()->getLibelle() === Etat::OUVERTE and $participant === $sortie->getOrganisateur()) {
            return true; // peut annuler
        } else {
            return false; // peut pas annuler
        }
    }
}
