<?php

declare(strict_types=1);

namespace App\Exception;

use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\Validator\ConstraintViolationListInterface;

class ValidationException extends HttpException
{
    public function __construct(
        private readonly ConstraintViolationListInterface $errors,
        $message = 'Validation failed',
    ) {
        parent::__construct(422, $message);
    }

    public function errors(): array
    {
        $result = [];
        foreach ($this->errors as $error) {
            $result[$error->getPropertyPath()][] = $error->getMessage();
        }

        return $result;
    }
}
