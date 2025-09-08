<?php

namespace App\Entity;

use App\Repository\EtatRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: EtatRepository::class)]
class Etat
{
    public const CREEE = "Créée" ;
    public const OUVERTE = "Ouverte" ;
    public const CLOTURE = "Clôturée" ;
    public const ACTENCOURS = "Activité en cours" ;
    public const ACTTERMINER = "Activité terminée" ;
    public const ACTARCHIVE = "Activité archivée" ;
    public const ANNULE = "Annulée" ;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length:30)]
    private ?string $libelle = null;

    /**
     * @var Collection<int, Sortie>
     */
    #[ORM\OneToMany(targetEntity: Sortie::class, mappedBy: 'etat')]
    private Collection $sorties_etat;

    public function __construct()
    {
        $this->sorties_etat = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getLibelle(): ?string
    {
        return $this->libelle;
    }

    public function setLibelle(string $libelle): static
    {
        $this->libelle = $libelle;

        return $this;
    }

    /**
     * @return Collection<int, Sortie>
     */
    public function getSortiesEtat(): Collection
    {
        return $this->sorties_etat;
    }

    public function addSortiesEtat(Sortie $sortiesEtat): static
    {
        if (!$this->sorties_etat->contains($sortiesEtat)) {
            $this->sorties_etat->add($sortiesEtat);
            $sortiesEtat->setEtat($this);
        }

        return $this;
    }

    public function removeSortiesEtat(Sortie $sortiesEtat): static
    {
        if ($this->sorties_etat->removeElement($sortiesEtat)) {
            // set the owning side to null (unless already changed)
            if ($sortiesEtat->getEtat() === $this) {
                $sortiesEtat->setEtat(null);
            }
        }

        return $this;
    }
}
