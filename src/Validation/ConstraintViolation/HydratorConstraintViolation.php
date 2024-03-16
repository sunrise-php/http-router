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
final class HydratorConstraintViolation implements ConstraintViolationInterface
{
    public function __construct(
        private readonly InvalidValueException $constraintViolation,
    ) {
    }

    public static function create(InvalidValueException $constraintViolation): self
    {
        return new self($constraintViolation);
    }

    public function getMessage(): string
    {
        return $this->constraintViolation->getMessage();
    }

    public function getMessageTemplate(): string
    {
        return $this->constraintViolation->getMessageTemplate();
    }

    public function getMessagePlaceholders(): array
    {
        return $this->constraintViolation->getMessagePlaceholders();
    }

    public function getPropertyPath(): string
    {
        return $this->constraintViolation->getPropertyPath();
    }

    public function getCode(): ?string
    {
        return $this->constraintViolation->getErrorCode();
    }
}
