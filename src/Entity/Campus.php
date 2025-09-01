<?php

namespace App\Entity;

use App\Repository\CampusRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: CampusRepository::class)]
class Campus
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $nom = null;

    /**
     * @var Collection<int, Participant>
     */
    #[ORM\OneToMany(targetEntity: Participant::class, mappedBy: 'campus')]
    private Collection $participants;

    /**
     * @var Collection<int, Sortie>
     */
    #[ORM\OneToMany(targetEntity: Sortie::class, mappedBy: 'campus')]
    private Collection $sorties_camp;

    public function __construct()
    {
        $this->participants = new ArrayCollection();
        $this->sorties_camp = new ArrayCollection();
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

    /**
     * @return Collection<int, Participant>
     */
    public function getParticipants(): Collection
    {
        return $this->participants;
    }

    public function addParticipant(Participant $participant): static
    {
        if (!$this->participants->contains($participant)) {
            $this->participants->add($participant);
            $participant->setCampus($this);
        }

        return $this;
    }

    public function removeParticipant(Participant $participant): static
    {
        if ($this->participants->removeElement($participant)) {
            // set the owning side to null (unless already changed)
            if ($participant->getCampus() === $this) {
                $participant->setCampus(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Sortie>
     */
    public function getSortiesCamp(): Collection
    {
        return $this->sorties_camp;
    }

    public function addSortiesCamp(Sortie $sortiesCamp): static
    {
        if (!$this->sorties_camp->contains($sortiesCamp)) {
            $this->sorties_camp->add($sortiesCamp);
            $sortiesCamp->setCampus($this);
        }

        return $this;
    }

    public function removeSortiesCamp(Sortie $sortiesCamp): static
    {
        if ($this->sorties_camp->removeElement($sortiesCamp)) {
            // set the owning side to null (unless already changed)
            if ($sortiesCamp->getCampus() === $this) {
                $sortiesCamp->setCampus(null);
            }
        }

        return $this;
    }
}
