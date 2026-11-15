<?php

declare(strict_types=1);

namespace App\Request;

use App\Exception\ValidationException;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Serializer\Exception\PartialDenormalizationException;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Validator\ConstraintViolation;
use Symfony\Component\Validator\ConstraintViolationList;

readonly class JsonRequest
{
    public function __construct(
        private RequestStack $request,
        private DenormalizerInterface $denormalizer,
    ) {
    }

    /**
     * @template T of object
     *
     * @param class-string<T> $class
     * @param bool            $allowEmpty when true, an empty body (or JSON null) yields a default instance
     *
     * @return T
     *
     * @throws BadRequestHttpException when the body is missing, not valid JSON or not a JSON object
     * @throws ValidationException     when a field has the wrong type
     */
    public function denormalize(string $class, bool $allowEmpty = false): object
    {
        $content = trim($this->request->getCurrentRequest()->getContent());

        if ($content === '') {
            if ($allowEmpty) {
                return new $class();
            }

            throw new BadRequestHttpException('Request body is empty.');
        }

        try {
            $data = json_decode($content, true, flags: JSON_THROW_ON_ERROR);
        } catch (\JsonException $e) {
            throw new BadRequestHttpException(sprintf('Invalid JSON: %s.', $e->getMessage()), $e);
        }

        if ($data === null && $allowEmpty) {
            return new $class();
        }

        // `[]` decodes the same as `{}`, so only a non-empty list is rejected.
        if (!is_array($data) || ($data !== [] && array_is_list($data))) {
            throw new BadRequestHttpException('Request body must be a JSON object.');
        }

        try {
            return $this->denormalizer->denormalize($data, $class, context: [
                DenormalizerInterface::COLLECT_DENORMALIZATION_ERRORS => true,
            ]);
        } catch (PartialDenormalizationException $e) {
            $violations = new ConstraintViolationList();
            foreach ($e->getErrors() as $error) {
                $violations->add(new ConstraintViolation(
                    message: sprintf(
                        'This value should be of type %s, %s given.',
                        implode('|', $error->getExpectedTypes() ?? ['unknown']),
                        $error->getCurrentType() ?? 'unknown',
                    ),
                    messageTemplate: null,
                    parameters: [],
                    root: null,
                    propertyPath: $error->getPath(),
                    invalidValue: null,
                ));
            }

            throw new ValidationException($violations);
        }
    }
}
