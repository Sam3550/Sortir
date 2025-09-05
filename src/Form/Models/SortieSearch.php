<?php

namespace App\Form\Models;

use Symfony\Component\Validator\Constraints as Assert;

class SortieSearch
{

    private ?string $campus = null;

    #[Assert\Length(
        max: 255,
        maxMessage: 'Le nom de la sortie ne peut pas dépasser {{ limit }} caractères'
    )]
    private ?string $sortieNom = null;

    #[Assert\Type(
        type: '\DateTimeInterface',
        message: 'La première date doit être une date valide'
    )]
    private ?\DateTimeInterface $premiereDate = null;

    #[Assert\Type(
        type: '\DateTimeInterface',
        message: 'La dernière date doit être une date valide'
    )]
    #[Assert\GreaterThanOrEqual(
        propertyPath: 'premiereDate',
        message: 'La dernière date doit être postérieure ou égale à la première date'
    )]
    private ?\DateTimeInterface $derniereDate = null;


    public bool $sortiesOrganisees = false;

    public bool $sortiesInscrites = false;

    public bool $sortiesNonInscrites = false;


    public bool $sortiesPassees = false;

    // Getters
    public function getCampus(): ?string
    {
        return $this->campus;
    }

    public function getSortieNom(): ?string
    {
        return $this->sortieNom;
    }

    public function getPremiereDate(): ?\DateTimeInterface
    {
        return $this->premiereDate;
    }

    public function getDerniereDate(): ?\DateTimeInterface
    {
        return $this->derniereDate;
    }

    // Setters
    public function setCampus(?string $campus): self
    {
        $this->campus = $campus;
        return $this;
    }

    public function setSortieNom(?string $sortieNom): self
    {
        $this->sortieNom = $sortieNom;
        return $this;
    }

    public function setPremiereDate(?\DateTimeInterface $premiereDate): self
    {
        $this->premiereDate = $premiereDate;
        return $this;
    }

    public function setDerniereDate(?\DateTimeInterface $derniereDate): self
    {
        $this->derniereDate = $derniereDate;
        return $this;
    }
    public function isSortiesOrganisees(): bool
    {
        return $this->sortiesOrganisees;
    }

    public function setSortiesOrganisees(bool $sortiesOrganisees): void
    {
        $this->sortiesOrganisees = $sortiesOrganisees;
    }

    public function isSortiesInscrites(): bool
    {
        return $this->sortiesInscrites;
    }

    public function setSortiesInscrites(bool $sortiesInscrites): void
    {
        $this->sortiesInscrites = $sortiesInscrites;
    }

    public function isSortiesNonInscrites(): bool
    {
        return $this->sortiesNonInscrites;
    }

    public function setSortiesNonInscrites(bool $sortiesNonInscrites): void
    {
        $this->sortiesNonInscrites = $sortiesNonInscrites;
    }

    public function isSortiesPassees(): bool
    {
        return $this->sortiesPassees;
    }

    public function setSortiesPassees(bool $sortiesPassees): void
    {
        $this->sortiesPassees = $sortiesPassees;
    }

    // Méthode utilitaire pour vérifier si le formulaire a des critères de recherche
    public function hasSearchCriteria(): bool
    {
        return $this->campus !== null
            || $this->sortieNom !== null
            || $this->premiereDate !== null
            || $this->derniereDate !== null;
    }

    // Méthode pour réinitialiser tous les critères
    public function reset(): self
    {
        $this->campus = null;
        $this->sortieNom = null;
        $this->premiereDate = null;
        $this->derniereDate = null;
        $this->sortiesOrganisees = false;
        $this->sortiesInscrites = false;
        $this->sortiesNonInscrites = false;
        $this->sortiesPassees = false;

        return $this;
    }
}