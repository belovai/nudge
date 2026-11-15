<?php

declare(strict_types=1);

namespace App\EventListener;

use App\Exception\ValidationException;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\Serializer\NameConverter\NameConverterInterface;

class ValidationExceptionListener
{
    public function __construct(
        // Same converter the serializer uses, so error keys match request field names (initialVersion -> initial_version).
        #[Autowire(service: 'serializer.name_converter.camel_case_to_snake_case')]
        private readonly NameConverterInterface $nameConverter,
    ) {
    }

    public function onKernelException(ExceptionEvent $event): void
    {
        $exception = $event->getThrowable();

        if (!$exception instanceof ValidationException) {
            return;
        }

        $errors = [];
        foreach ($exception->errors() as $propertyPath => $messages) {
            $errors[$this->nameConverter->normalize((string) $propertyPath)] = $messages;
        }

        $response = new JsonResponse([
            'success' => false,
            'message' => $exception->getMessage(),
            'errors' => $errors,
        ], 422);

        $event->setResponse($response);
    }
}
