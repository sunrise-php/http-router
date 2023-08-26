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

namespace Sunrise\Http\Router\TypeConversion\TypeConverter;

use Generator;
use ReflectionNamedType;
use ReflectionType;
use Sunrise\Http\Router\Exception\InvalidArgumentException;

use function filter_var;
use function is_int;
use function is_string;
use function trim;

use const FILTER_NULL_ON_FAILURE;
use const FILTER_VALIDATE_INT;

/**
 * @since 3.0.0
 */
final class IntTypeConverter implements TypeConverterInterface
{

    /**
     * @inheritDoc
     *
     * @throws InvalidArgumentException If the value isn't valid.
     */
    public function castValue(mixed $value, ReflectionType $type): Generator
    {
        if (! $type instanceof ReflectionNamedType || $type->getName() !== 'int') {
            return;
        }

        if (is_string($value)) {
            // As part of the support for HTML forms and other untyped data sources,
            // an empty string should not be cast to an integer type,
            // therefore, such values should be treated as NULL.
            if (trim($value) === '') {
                // phpcs:ignore Generic.Files.LineLength
                return $type->allowsNull() ? yield : throw new InvalidArgumentException('This value must not be empty.');
            }

            // https://github.com/php/php-src/blob/b7d90f09d4a1688f2692f2fa9067d0a07f78cc7d/ext/filter/logical_filters.c#L94
            // https://github.com/php/php-src/blob/b7d90f09d4a1688f2692f2fa9067d0a07f78cc7d/ext/filter/logical_filters.c#L197
            $value = filter_var($value, FILTER_VALIDATE_INT, FILTER_NULL_ON_FAILURE);
        }

        if (!is_int($value)) {
            throw new InvalidArgumentException('This value must be of type integer.');
        }

        yield $value;
    }
}
