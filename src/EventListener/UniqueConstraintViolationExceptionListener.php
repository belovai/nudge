<?php

declare(strict_types=1);

namespace App\EventListener;

use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;

/**
 * Safety net behind the project row lock: if two writers still collide on a
 * unique version/build, answer 409 so the client can retry instead of a 500.
 */
class UniqueConstraintViolationExceptionListener
{
    public function onKernelException(ExceptionEvent $event): void
    {
        if (!$event->getThrowable() instanceof UniqueConstraintViolationException) {
            return;
        }

        $event->setResponse(new JsonResponse([
            'success' => false,
            'message' => 'Conflict with a concurrent request, please retry.',
        ], 409));
    }
}
