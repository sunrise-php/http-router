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

use Sunrise\Http\Router\Helper\ValidatorHelper;

/**
 * @since 3.0.0
 */
final class ValidatorConstraintViolation implements \Sunrise\Http\Router\Validation\ConstraintViolationInterface
{
    public function __construct(
        private readonly \Symfony\Component\Validator\ConstraintViolationInterface $constraintViolation,
    ) {
    }

    public static function create(\Symfony\Component\Validator\ConstraintViolationInterface $constraintViolation): self
    {
        return new self($constraintViolation);
    }

    public function getMessage(): string
    {
        return (string) $this->constraintViolation->getMessage();
    }

    public function getMessageTemplate(): string
    {
        return $this->constraintViolation->getMessageTemplate();
    }

    public function getMessagePlaceholders(): array
    {
        return $this->constraintViolation->getParameters();
    }

    public function getPropertyPath(): string
    {
        return ValidatorHelper::adaptPropertyPath($this->constraintViolation->getPropertyPath());
    }

    public function getCode(): ?string
    {
        return $this->constraintViolation->getCode();
    }
}
