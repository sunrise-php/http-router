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
use UnexpectedValueException;

use function filter_var;
use function is_bool;
use function is_string;
use function trim;

use const FILTER_NULL_ON_FAILURE;
use const FILTER_VALIDATE_BOOL;

/**
 * @since 3.0.0
 */
final class BoolTypeConverter implements TypeConverterInterface
{

    /**
     * @inheritDoc
     */
    public function castValue(mixed $value, ReflectionType $type): Generator
    {
        if (! $type instanceof ReflectionNamedType || $type->getName() !== 'bool') {
            return;
        }

        if (is_string($value)) {
            // As part of the support for HTML forms and other untyped data sources,
            // an empty string should not be cast to a boolean type,
            // therefore, such values should be treated as NULL.
            if (trim($value) === '') {
                // phpcs:ignore Generic.Files.LineLength
                return $type->allowsNull() ? yield : throw new UnexpectedValueException('This value must not be empty.');
            }

            // https://github.com/php/php-src/blob/b7d90f09d4a1688f2692f2fa9067d0a07f78cc7d/ext/filter/logical_filters.c#L273
            $value = filter_var($value, FILTER_VALIDATE_BOOL, FILTER_NULL_ON_FAILURE);
        }

        if (!is_bool($value)) {
            throw new UnexpectedValueException('This value must be of type boolean.');
        }

        yield $value;
    }
}
