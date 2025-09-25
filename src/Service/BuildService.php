<?php
declare(strict_types=1);

namespace App\Service;

use App\Dto\CreateBuildDto;
use App\Entity\Build;
use App\Entity\Version;
use App\Exception\ValidationException;
use App\Repository\BuildRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class BuildService
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly ValidatorInterface $validator,
        private readonly BuildRepository $buildRepository,
    ) {
    }

    public function createBuild(Version $version, CreateBuildDto $dto): Build
    {
        $errors = $this->validator->validate($dto);
        if (count($errors) > 0) {
            throw new ValidationException($errors);
        }

        $latestBuild = $this->buildRepository->getLatestBuildForTag($version->getId(), $dto->tag);
        return $this->bumpRevision($version, $dto, $latestBuild ? $latestBuild->getRevision() + 1 : 1);
    }

    private function bumpRevision(Version $version, CreateBuildDto $dto, int $revision): Build
    {
        $build = new Build($dto->tag, $revision, $dto->context);
        $version->addBuild($build);
        $this->entityManager->persist($build);
        $this->entityManager->flush();

        return $build;
    }
}
