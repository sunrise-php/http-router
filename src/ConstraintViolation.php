<?php

/**
 * It's free open-source software released under the MIT License.
 *
 * @author Anatoly Nekhay <afenric@gmail.com>
 * @copyright Copyright (c) 2018, Anatoly Nekhay
 * @license https://github.com/sunrise-php/http-router/blob/master/LICENSE
 * @link https://github.com/sunrise-php/http-router
 */

declare(strict_types=1);

namespace Sunrise\Http\Router;

use Sunrise\Hydrator\Exception\InvalidValueException as HydratorConstraintViolation;
use Symfony\Component\Validator\ConstraintViolationInterface as ValidatorConstraintViolation;

/**
 * @since 3.0.0
 */
final class ConstraintViolation implements ConstraintViolationInterface
{
    public function __construct(
        private readonly string $message,
        private readonly string $messageTemplate,
        private readonly array $messagePlaceholders,
        private readonly string $propertyPath,
        private readonly ?string $code,
    ) {
    }

    public static function fromHydrator(HydratorConstraintViolation $hydratorConstraintViolation): self
    {
        return new self(
            $hydratorConstraintViolation->getMessage(),
            $hydratorConstraintViolation->getMessageTemplate(),
            $hydratorConstraintViolation->getMessagePlaceholders(),
            $hydratorConstraintViolation->getPropertyPath(),
            $hydratorConstraintViolation->getErrorCode(),
        );
    }

    public static function fromValidator(ValidatorConstraintViolation $validatorConstraintViolation): self
    {
        return new self(
            (string) $validatorConstraintViolation->getMessage(),
            $validatorConstraintViolation->getMessageTemplate(),
            $validatorConstraintViolation->getParameters(),
            $validatorConstraintViolation->getPropertyPath(),
            $validatorConstraintViolation->getCode(),
        );
    }

    public function getMessage(): string
    {
        return $this->message;
    }

    public function getMessageTemplate(): string
    {
        return $this->messageTemplate;
    }

    public function getMessagePlaceholders(): array
    {
        return $this->messagePlaceholders;
    }

    public function getPropertyPath(): string
    {
        return $this->propertyPath;
    }

    public function getCode(): ?string
    {
        return $this->code;
    }
}
