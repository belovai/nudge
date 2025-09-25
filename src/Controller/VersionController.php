<?php

declare(strict_types=1);

namespace App\Controller;

use App\Dto\CreateVersionDto;
use App\Entity\Project;
use App\Repository\VersionRepository;
use App\Request\JsonRequest;
use App\Service\VersionService;
use Symfony\Bridge\Doctrine\Attribute\MapEntity;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api', name: 'api_')]
final class VersionController extends AbstractController
{
    public function __construct(
        private readonly JsonRequest $jsonRequest,
        private readonly VersionService $versionService,
        private readonly VersionRepository $versionRepository,
    ) {
    }

    #[Route('/{uuid}', name: 'app_versions_show', methods: ['GET'])]
    public function show(
        #[MapEntity(mapping: ['uuid' => 'uuid'])] Project $project,
    ): JsonResponse {
        $version = $this->versionRepository->getCurrentVersion($project->getUuid());

        return $this->json(
            data: [
                'success' => true,
                'data' => $version,
            ],
            status: Response::HTTP_OK,
            context: ['groups' => 'version:public'],
        );
    }

    #[Route('/{uuid}/patch', name: 'app_versions_patch', methods: ['POST'])]
    public function patch(
        #[MapEntity(mapping: ['uuid' => 'uuid'])] Project $project,
    ): JsonResponse {
        $dto = $this->jsonRequest->denormalize(CreateVersionDto::class, true);
        $version = $this->versionService->bumpPatch($project, $dto);

        return $this->json(
            data: [
                'success' => true,
                'data' => $version,
            ],
            status: Response::HTTP_CREATED,
            context: ['groups' => 'version:created'],
        );
    }

    #[Route('/{uuid}/minor', name: 'app_versions_minor', methods: ['POST'])]
    public function minor(
        #[MapEntity(mapping: ['uuid' => 'uuid'])] Project $project,
    ): JsonResponse {
        $dto = $this->jsonRequest->denormalize(CreateVersionDto::class, true);
        $version = $this->versionService->bumpMinor($project, $dto);

        return $this->json(
            data: [
                'success' => true,
                'data' => $version,
            ],
            status: Response::HTTP_CREATED,
            context: ['groups' => 'version:created'],
        );
    }

    #[Route('/{uuid}/major', name: 'app_versions_major', methods: ['POST'])]
    public function major(
        #[MapEntity(mapping: ['uuid' => 'uuid'])] Project $project,
    ): JsonResponse {
        $dto = $this->jsonRequest->denormalize(CreateVersionDto::class, true);
        $version = $this->versionService->bumpMajor($project, $dto);

        return $this->json(
            data: [
                'success' => true,
                'data' => $version,
            ],
            status: Response::HTTP_CREATED,
            context: ['groups' => 'version:created'],
        );
    }
}
