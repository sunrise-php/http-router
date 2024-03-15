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
use Sunrise\Http\Router\Validation\ConstraintViolationInterface as RouterConstraintViolationInterface;
use Symfony\Component\Validator\ConstraintViolationInterface as ValidatorConstraintViolationInterface;

/**
 * @since 3.0.0
 */
final class ValidatorConstraintViolationProxy implements RouterConstraintViolationInterface
{
    public function __construct(private readonly ValidatorConstraintViolationInterface $validatorConstraintViolation)
    {
    }

    public static function create(ValidatorConstraintViolationInterface $validatorConstraintViolation): self
    {
        return new self($validatorConstraintViolation);
    }

    public function getMessage(): string
    {
        return (string) $this->validatorConstraintViolation->getMessage();
    }

    public function getMessageTemplate(): string
    {
        return $this->validatorConstraintViolation->getMessageTemplate();
    }

    public function getMessagePlaceholders(): array
    {
        return $this->validatorConstraintViolation->getParameters();
    }

    public function getPropertyPath(): string
    {
        return ValidatorHelper::adaptPropertyPath($this->validatorConstraintViolation->getPropertyPath());
    }

    public function getCode(): ?string
    {
        return $this->validatorConstraintViolation->getCode();
    }
}
