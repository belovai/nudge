<?php

declare(strict_types=1);

namespace App\Dto;

use Symfony\Component\Validator\Constraints as Assert;

class CreateProjectDto
{
    #[Assert\NotBlank]
    #[Assert\Length(max: 255)]
    public string $name;

    #[Assert\Regex(pattern: '/^\d+\.\d+\.\d+$/')]
    public string $initialVersion = '0.0.1';
}
