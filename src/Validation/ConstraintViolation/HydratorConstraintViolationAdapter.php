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

namespace Sunrise\Http\Router\Validation\ConstraintViolation;

use Sunrise\Http\Router\Validation\ConstraintViolationInterface;
use Sunrise\Hydrator\Exception\InvalidValueException;

/**
 * @since 3.0.0
 */
final class HydratorConstraintViolationAdapter implements ConstraintViolationInterface
{
    public function __construct(
        private readonly InvalidValueException $hydratorConstraintViolation,
    ) {
    }

    public static function create(InvalidValueException $hydratorConstraintViolation): self
    {
        return new self($hydratorConstraintViolation);
    }

    public function getMessage(): string
    {
        return $this->hydratorConstraintViolation->getMessage();
    }

    public function getMessageTemplate(): string
    {
        return $this->hydratorConstraintViolation->getMessageTemplate();
    }

    /**
     * @inheritDoc
     */
    public function getMessagePlaceholders(): array
    {
        return $this->hydratorConstraintViolation->getMessagePlaceholders();
    }

    public function getPropertyPath(): string
    {
        return $this->hydratorConstraintViolation->getPropertyPath();
    }

    public function getCode(): ?string
    {
        return $this->hydratorConstraintViolation->getErrorCode();
    }

    public function getInvalidValue(): mixed
    {
        return $this->hydratorConstraintViolation->getInvalidValue();
    }
}
