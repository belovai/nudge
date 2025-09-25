<?php

declare(strict_types=1);

namespace App\Service;

use App\Dto\CreateProjectDto;
use App\Entity\Project;
use App\Exception\ValidationException;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class ProjectService
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly ValidatorInterface $validator,
        private readonly VersionService $versionService,
    ) {
    }

    public function createProject(CreateProjectDto $dto): Project
    {
        $errors = $this->validator->validate($dto);
        if (count($errors) > 0) {
            throw new ValidationException($errors);
        }

        $project = new Project($dto->name);
        $this->entityManager->persist($project);
        $this->entityManager->flush();

        $this->versionService->createInitialVersion($project, $dto->initialVersion);

        return $project;
    }
}
