<?php

namespace App\Dto;

use App\Entity\Campus;
use Symfony\Component\Validator\Constraints as Assert;

class RegistrationFormDto
{
    #[Assert\NotBlank]
    #[Assert\Length(min: 2, max: 50)]
    public ?string $nom = null;

    #[Assert\NotBlank]
    #[Assert\Length(min: 2, max: 50)]
    public ?string $prenom = null;

    public ?string $telephone = null;

    #[Assert\NotNull]
    public ?Campus $campus = null;

    #[Assert\NotBlank]
    #[Assert\Length(min: 6)]
    public ?string $plainPassword = null;
}
