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

use Sunrise\Http\Router\Validation\ConstraintViolationInterface as RouterConstraintViolationInterface;
use Symfony\Component\Validator\ConstraintViolationInterface as ValidatorConstraintViolationInterface;

use function preg_replace;

/**
 * @since 3.0.0
 */
final class ValidatorConstraintViolationAdapter implements RouterConstraintViolationInterface
{
    public function __construct(
        private readonly ValidatorConstraintViolationInterface $validatorConstraintViolation,
    ) {
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
        return self::adaptPropertyPath($this->validatorConstraintViolation->getPropertyPath());
    }

    public function getCode(): ?string
    {
        return $this->validatorConstraintViolation->getCode();
    }

    private static function adaptPropertyPath(string $propertyPath): string
    {
        return preg_replace(['/\x5b([^\x5b\x5d]+)\x5d/', '/^\x2e/'], ['.$1'], $propertyPath);
    }
}
