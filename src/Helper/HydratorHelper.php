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

namespace Sunrise\Http\Router\Helper;

use Generator;
use Sunrise\Http\Router\ConstraintViolation;
use Sunrise\Http\Router\ConstraintViolationInterface;
use Sunrise\Hydrator\Exception\InvalidDataException;
use Sunrise\Hydrator\Exception\InvalidValueException;

/**
 * @since 3.0.0
 */
final class HydratorHelper
{
    /**
     * @return Generator<int, ConstraintViolationInterface>
     */
    public static function adaptHydratorConstraintViolations(InvalidDataException|InvalidValueException $error): Generator
    {
        if ($error instanceof InvalidValueException) {
            return yield new ConstraintViolation(
                $error->getMessage(),
                $error->getMessageTemplate(),
                $error->getMessagePlaceholders(),
                $error->getPropertyPath(),
                $error->getErrorCode(),
            );
        }

        foreach ($error->getExceptions() as $violation) {
            yield new ConstraintViolation(
                $violation->getMessage(),
                $violation->getMessageTemplate(),
                $violation->getMessagePlaceholders(),
                $violation->getPropertyPath(),
                $violation->getErrorCode(),
            );
        }
    }
}
