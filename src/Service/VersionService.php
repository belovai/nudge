<?php

declare(strict_types=1);

namespace App\Service;

use App\Entity\Project;
use App\Entity\Version;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class VersionService
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly ValidatorInterface $validator,
    ) {
    }

    public function createInitialVersion(Project $project, string $initialVersion): Version
    {
        $version = new Version($initialVersion);
        $version->setProject($project);

        $this->entityManager->persist($version);
        $this->entityManager->flush();

        return $version;
    }
}
