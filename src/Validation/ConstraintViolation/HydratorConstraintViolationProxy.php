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
use Sunrise\Hydrator\Exception\InvalidValueException as HydratorConstraintViolation;

/**
 * @since 3.0.0
 */
final class HydratorConstraintViolationProxy implements ConstraintViolationInterface
{
    public function __construct(private readonly HydratorConstraintViolation $hydratorConstraintViolation)
    {
    }

    /**
     * Creates the {@see ConstraintViolationInterface} object(s) from the given hydrator's constraint violation(s)
     *
     * @return Generator<int, ConstraintViolationInterface>
     */
    public static function create(HydratorConstraintViolation ...$hydratorConstraintViolations): Generator
    {
        foreach ($hydratorConstraintViolations as $hydratorConstraintViolation) {
            yield new self($hydratorConstraintViolation);
        }
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
}
