<?php

declare(strict_types=1);

namespace App\Service;

use App\Dto\CreateBuildDto;
use App\Entity\Build;
use App\Entity\Version;
use App\Exception\ValidationException;
use App\Repository\BuildRepository;
use App\Repository\ProjectRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class BuildService
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly ValidatorInterface $validator,
        private readonly BuildRepository $buildRepository,
        private readonly ProjectRepository $projectRepository,
    ) {
    }

    public function createBuild(Version $version, CreateBuildDto $dto): Build
    {
        $errors = $this->validator->validate($dto);
        if (count($errors) > 0) {
            throw new ValidationException($errors);
        }

        // Lock first, then read the latest revision, so two concurrent builds can't get the same revision.
        return $this->entityManager->wrapInTransaction(function () use ($version, $dto): Build {
            $this->projectRepository->lockForUpdate($version->getProject());

            $latestBuild = $this->buildRepository->getLatestBuildForTag($version->getId(), $dto->tag);
            $revision = $latestBuild ? $latestBuild->getRevision() + 1 : 1;

            $build = new Build($dto->tag, $revision, $dto->context);
            $version->addBuild($build);
            $this->entityManager->persist($build);

            return $build;
        });
    }
}
