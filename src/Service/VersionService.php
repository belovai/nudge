<?php

declare(strict_types=1);

namespace App\Service;

use App\Dto\CreateVersionDto;
use App\Entity\Project;
use App\Entity\Version;
use App\Exception\ValidationException;
use App\Repository\VersionRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class VersionService
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly ValidatorInterface $validator,
        private readonly VersionRepository $versionRepository,
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

    public function bumpPatch(Project $project, CreateVersionDto $dto): Version
    {
        $errors = $this->validator->validate($dto);
        if (count($errors) > 0) {
            throw new ValidationException($errors);
        }
        $currentVersion = $this->versionRepository->getCurrentVersion($project->getUuid());

        $version = new Version(
            $this->bump($currentVersion, 'patch'),
            $dto->context
        );
        $version->setProject($project);

        $this->entityManager->persist($version);
        $this->entityManager->flush();

        return $version;
    }

    public function bumpMinor(Project $project, CreateVersionDto $dto): Version
    {
        $errors = $this->validator->validate($dto);
        if (count($errors) > 0) {
            throw new ValidationException($errors);
        }
        $currentVersion = $this->versionRepository->getCurrentVersion($project->getUuid());

        $version = new Version(
            $this->bump($currentVersion, 'minor'),
            $dto->context
        );
        $version->setProject($project);

        $this->entityManager->persist($version);
        $this->entityManager->flush();

        return $version;
    }

    public function bumpMajor(Project $project, CreateVersionDto $dto): Version
    {
        $errors = $this->validator->validate($dto);
        if (count($errors) > 0) {
            throw new ValidationException($errors);
        }
        $currentVersion = $this->versionRepository->getCurrentVersion($project->getUuid());

        $version = new Version(
            $this->bump($currentVersion, 'major'),
            $dto->context
        );
        $version->setProject($project);

        $this->entityManager->persist($version);
        $this->entityManager->flush();

        return $version;
    }

    private function bump(Version $version, string $part): string
    {
        $parts = explode('.', $version->getVersion());

        switch ($part) {
            case 'major':
                $parts[0]++;
                $parts[1] = 0;
                $parts[2] = 0;
                break;
            case 'minor':
                $parts[1]++;
                $parts[2] = 0;
                break;
            case 'patch':
                $parts[2] = intval(explode('-', $parts[2])[0]) + 1;
                break;
            default:
                throw new \Exception("Invalid part type. Expected 'major', 'minor' or 'patch'.");
        }

        return implode('.', $parts);
    }
}
