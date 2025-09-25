<?php
declare(strict_types=1);

namespace App\Dto;

use Symfony\Component\Validator\Constraints as Assert;

class CreateBuildDto
{
    #[Assert\NotBlank]
    #[Assert\Length(max: 255)]
    public string $tag;

    public ?array $context = null;
}
