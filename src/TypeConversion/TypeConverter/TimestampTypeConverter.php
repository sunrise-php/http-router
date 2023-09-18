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

use DateTimeImmutable;
use DateTimeInterface;
use Exception;
use Generator;
use ReflectionNamedType;
use ReflectionType;
use UnexpectedValueException;

use function ctype_digit;
use function is_a;
use function is_int;
use function is_string;
use function trim;

/**
 * @since 3.0.0
 */
final class TimestampTypeConverter implements TypeConverterInterface
{

    /**
     * @inheritDoc
     */
    public function castValue(mixed $value, ReflectionType $type): Generator
    {
        if (! $type instanceof ReflectionNamedType) {
            return;
        }

        $className = $type->getName();

        if (!is_a($className, DateTimeInterface::class, true)) {
            return;
        }

        if (is_int($value)) {
            $value = '@' . $value;
        }

        if (!is_string($value)) {
            throw new UnexpectedValueException('This value must be of a string or an integer type.');
        }

        $value = trim($value);

        // As part of the support for HTML forms and other untyped data sources,
        // an instance of DateTime should not be created from an empty string,
        // therefore, such values should be treated as NULL.
        if ($value === '') {
            return $type->allowsNull() ? yield : throw new UnexpectedValueException('This value must not be empty.');
        }

        if (ctype_digit($value)) {
            $value = '@' . $value;
        }

        // It is recommended to use only DateTimeImmutable...
        if ($className === DateTimeInterface::class) {
            $className = DateTimeImmutable::class;
        }

        try {
            yield new $className($value);
        } catch (Exception $e) {
            throw new UnexpectedValueException('This value is not a valid timestamp.', previous: $e);
        }
    }
}
