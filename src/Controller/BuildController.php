<?php

declare(strict_types=1);

namespace App\Controller;

use App\Dto\CreateBuildDto;
use App\Entity\Version;
use App\Request\JsonRequest;
use App\Service\BuildService;
use Symfony\Bridge\Doctrine\Attribute\MapEntity;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api', name: 'api_')]
final class BuildController extends AbstractController
{
    public function __construct(
        private readonly JsonRequest $jsonRequest,
        private readonly BuildService $buildService,
    ) {
    }

    #[Route('/{uuid}/{version}/builds', name: 'app_builds_store', methods: ['POST'])]
    public function store(
        #[MapEntity(mapping: ['uuid' => 'project', 'version' => 'version'])] Version $version,
    ): JsonResponse {
        $dto = $this->jsonRequest->denormalize(CreateBuildDto::class);
        $build = $this->buildService->createBuild($version, $dto);

        return $this->json(
            data: [
                'success' => true,
                'data' => [
                    'version' => $build->displayVersion,
                    'context' => $build->getContext(),
                ],
            ],
            status: Response::HTTP_CREATED,
            context: ['groups' => 'build:public'],
        );
    }
}
