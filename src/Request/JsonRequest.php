<?php

declare(strict_types=1);

namespace App\Request;

use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Serializer\SerializerInterface;

readonly class JsonRequest
{
    public function __construct(
        private RequestStack $request,
        private SerializerInterface $serializer,
    ) {
    }

    public function denormalize(string $class): object
    {
        $data = json_decode($this->request->getCurrentRequest()->getContent(), true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new \InvalidArgumentException('Invalid JSON data');
        }

        if (empty($data)) {
            throw new \InvalidArgumentException('Request body is empty');
        }

        return $this->serializer->denormalize($data, $class);
    }
}
