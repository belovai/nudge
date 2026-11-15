<?php

declare(strict_types=1);

namespace App\EventListener;

use App\Exception\ValidationException;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Serializer\NameConverter\NameConverterInterface;

/**
 * Renders every client-facing error as {"success": false, "message": ...}.
 *
 * Anything else (a bug, a 500) is left to Symfony, so it is logged and rendered
 * by the framework's error handler.
 */
#[AsEventListener(event: KernelEvents::EXCEPTION)]
final readonly class JsonExceptionListener
{
    public function __construct(
        // Same converter the serializer uses, so error keys match request field names (initialVersion -> initial_version).
        #[Autowire(service: 'serializer.name_converter.camel_case_to_snake_case')]
        private NameConverterInterface $nameConverter,
    ) {
    }

    public function __invoke(ExceptionEvent $event): void
    {
        $exception = $event->getThrowable();

        $response = match (true) {
            $exception instanceof ValidationException => $this->validationError($exception),
            // Safety net behind the project row lock: two writers still collided on a unique version/build.
            $exception instanceof UniqueConstraintViolationException => $this->error('Conflict with a concurrent request, please retry.', 409),
            $exception instanceof HttpExceptionInterface => $this->error($exception->getMessage(), $exception->getStatusCode(), $exception->getHeaders()),
            default => null,
        };

        if ($response !== null) {
            $event->setResponse($response);
        }
    }

    private function validationError(ValidationException $exception): JsonResponse
    {
        $errors = [];
        foreach ($exception->errors() as $propertyPath => $messages) {
            $errors[$this->nameConverter->normalize((string) $propertyPath)] = $messages;
        }

        return new JsonResponse([
            'success' => false,
            'message' => $exception->getMessage(),
            'errors' => $errors,
        ], $exception->getStatusCode());
    }

    /**
     * @param array<string, string> $headers
     */
    private function error(string $message, int $status, array $headers = []): JsonResponse
    {
        return new JsonResponse(['success' => false, 'message' => $message], $status, $headers);
    }
}
