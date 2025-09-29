<?php

declare(strict_types=1);

namespace App\EventListener;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\Exception\TooManyRequestsHttpException;

class TooManyRequestsHttpExceptionListener
{
    public function onKernelException(ExceptionEvent $event): void
    {
        $exception = $event->getThrowable();

        if (!$exception instanceof TooManyRequestsHttpException) {
            return;
        }

        $response = new JsonResponse(
            [
                'success' => false,
                'message' => $exception->getMessage(),
            ],
            429,
            $exception->getHeaders()
        );

        $event->setResponse($response);
    }
}
