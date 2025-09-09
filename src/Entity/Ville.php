<?php

namespace App\Entity;

use App\Repository\VilleRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: VilleRepository::class)]
class Ville
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $nom = null;

    #[ORM\Column(length: 255)]
    private ?string $codePostal = null;

    /**
     * @var Collection<int, Lieu>
     */
    #[ORM\OneToMany(targetEntity: Lieu::class, mappedBy: 'ville')]
    private Collection $lieux_ville;

    public function __construct()
    {
        $this->lieux_ville = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNom(): ?string
    {
        return $this->nom;
    }

    public function setNom(string $nom): static
    {
        $this->nom = $nom;

        return $this;
    }

    public function getCodePostal(): ?string
    {
        return $this->codePostal;
    }

    public function setCodePostal(string $codePostal): static
    {
        $this->codePostal = $codePostal;

        return $this;
    }

    /**
     * @return Collection<int, Lieu>
     */
    public function getLieuxVille(): Collection
    {
        return $this->lieux_ville;
    }

    public function addLieuxVille(Lieu $lieuxVille): static
    {
        if (!$this->lieux_ville->contains($lieuxVille)) {
            $this->lieux_ville->add($lieuxVille);
            $lieuxVille->setVille($this);
        }

        return $this;
    }

    public function removeLieuxVille(Lieu $lieuxVille): static
    {
        if ($this->lieux_ville->removeElement($lieuxVille)) {
            // set the owning side to null (unless already changed)
            if ($lieuxVille->getVille() === $this) {
                $lieuxVille->setVille(null);
            }
        }

        return $this;
    }
}
