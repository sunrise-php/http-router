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

use Generator;
use Sunrise\Http\Router\Validation\ConstraintViolationInterface;
use Symfony\Component\Validator\ConstraintViolationInterface as ValidatorConstraintViolation;

/**
 * @since 3.0.0
 */
final class ValidatorErrorProxy implements ConstraintViolationInterface
{

    /**
     * Constructor of the class
     *
     * @param ValidatorConstraintViolation $validatorConstraintViolation
     */
    public function __construct(private ValidatorConstraintViolation $validatorConstraintViolation)
    {
    }

    /**
     * Creates the {@see ConstraintViolationInterface} object(s) from the given validator's constraint violation(s)
     *
     * @param ValidatorConstraintViolation ...$validatorConstraintViolations
     *
     * @return Generator<array-key, ConstraintViolationInterface>
     */
    public static function create(ValidatorConstraintViolation ...$validatorConstraintViolations): Generator
    {
        foreach ($validatorConstraintViolations as $validatorConstraintViolation) {
            yield new self($validatorConstraintViolation);
        }
    }

    /**
     * @inheritDoc
     */
    public function getMessage(): string
    {
        return $this->validatorConstraintViolation->getMessage();
    }

    /**
     * @inheritDoc
     */
    public function getMessageTemplate(): string
    {
        return $this->validatorConstraintViolation->getMessageTemplate();
    }

    /**
     * @inheritDoc
     */
    public function getMessagePlaceholders(): array
    {
        /** @var array<string, mixed> */
        return $this->validatorConstraintViolation->getParameters();
    }

    /**
     * @inheritDoc
     */
    public function getPropertyPath(): string
    {
        return $this->validatorConstraintViolation->getPropertyPath();
    }
}
