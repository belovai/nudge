<?php

declare(strict_types=1);

namespace App\Controller;

use App\Dto\CreateProjectDto;
use App\Request\JsonRequest;
use App\Service\ProjectService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api', name: 'api_')]
final class ProjectController extends AbstractController
{
    public function __construct(
        private readonly JsonRequest $jsonRequest,
        private readonly ProjectService $projectService,
    ) {
    }

    #[Route('/projects', name: 'app_projects', methods: ['POST'])]
    public function store(): JsonResponse
    {
        $dto = $this->jsonRequest->denormalize(CreateProjectDto::class);
        $project = $this->projectService->createProject($dto);

        return $this->json(
            data: [
                'success' => true,
                'data' => $project,
            ],
            status: Response::HTTP_CREATED,
            context: ['groups' => 'project:public'],
        );
    }
}
