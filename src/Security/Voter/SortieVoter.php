<?php

namespace App\Security\Voter;

use App\Entity\Etat;
use App\Entity\Sortie;
use Symfony\Bundle\SecurityBundle\Security;
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
    public const SUPPRIMER = 'SORTIE_SUPPRIMER';

    public function __construct(private readonly Security $security)
    {
    }

    protected function supports(string $attribute, mixed $subject): bool
    {
        return in_array($attribute, [self::AFFICHER, self::DESISTER, self::INSCRIRE, self::MODIFIER, self::PUBLIER, self::ANNULER, self::SUPPRIMER])
            && $subject instanceof \App\Entity\Sortie;
    }

    protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token): bool
    {
        $participant = $token->getUser();

        if (!$participant instanceof UserInterface) {
            return false;
        }

        if ($this->security->isGranted('ROLE_ADMIN')) {
            return true;
        }

        /**
         * @var Sortie $subject
         */

        return match ($attribute) {
            self::AFFICHER => $this->afficher($subject, $participant),
            self::DESISTER => $this->desister($subject, $participant),
            self::INSCRIRE => $this->inscrire($subject, $participant),
            self::MODIFIER => $this->modifier($subject, $participant),
            self::PUBLIER => $this->publier($subject, $participant),
            self::ANNULER => $this->annuler($subject, $participant),
            self::SUPPRIMER => $this->supprimer($subject, $participant),
            default => false,
        };
    }

    private function afficher(Sortie $sortie, UserInterface $participant): bool
    {
        return $sortie->getEtat()->getLibelle() !== Etat::CREEE || $participant === $sortie->getOrganisateur();
    }


    private function desister(Sortie $sortie, UserInterface $participant): bool
    {
        return $sortie->getParticipants()->contains($participant);
    }

    private function inscrire(Sortie $sortie, UserInterface $participant): bool
    {
        return !$sortie->getParticipants()->contains($participant) && $sortie->getEtat()->getLibelle() !== Etat::CREEE;
    }

    private function modifier(Sortie $sortie, UserInterface $participant): bool
    {
        return $sortie->getEtat()->getLibelle() === Etat::CREEE and $participant === $sortie->getOrganisateur();
    }

    private function publier(Sortie $sortie, UserInterface $participant): bool
    {
        return $sortie->getEtat()->getLibelle() === Etat::CREEE and $participant === $sortie->getOrganisateur();
    }

    private function annuler(Sortie $sortie, UserInterface $participant): bool
    {
        return $sortie->getEtat()->getLibelle() === Etat::OUVERTE and $participant === $sortie->getOrganisateur();
    }

    private function supprimer(Sortie $sortie, UserInterface $participant): bool
    {
        return $participant === $sortie->getOrganisateur();
    }
}
