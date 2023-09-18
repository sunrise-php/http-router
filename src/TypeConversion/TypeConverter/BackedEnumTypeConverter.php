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

use BackedEnum;
use Generator;
use ReflectionEnum;
use ReflectionNamedType;
use ReflectionType;
use UnexpectedValueException;
use ValueError;

use function filter_var;
use function is_int;
use function is_string;
use function is_subclass_of;
use function join;
use function sprintf;
use function trim;

use const FILTER_NULL_ON_FAILURE;
use const FILTER_VALIDATE_INT;
use const PHP_VERSION_ID;

/**
 * @since 3.0.0
 */
final class BackedEnumTypeConverter implements TypeConverterInterface
{

    /**
     * @inheritDoc
     */
    public function castValue(mixed $value, ReflectionType $type): Generator
    {
        if (PHP_VERSION_ID < 80100) {
            return;
        }

        if (! $type instanceof ReflectionNamedType) {
            return;
        }

        $enumName = $type->getName();

        if (!is_subclass_of($enumName, BackedEnum::class)) {
            return;
        }

        /** @var ReflectionNamedType $enumType */
        $enumType = (new ReflectionEnum($enumName))->getBackingType();

        /** @var 'int'|'string' $enumTypeName */
        $enumTypeName = $enumType->getName();

        if (is_string($value)) {
            // As part of the support for HTML forms and other untyped data sources,
            // an instance of Enum should not be created from an empty string,
            // therefore, such values should be treated as NULL.
            if (trim($value) === '') {
                // phpcs:ignore Generic.Files.LineLength
                return $type->allowsNull() ? yield : throw new UnexpectedValueException('This value must not be empty.');
            }

            if ($enumTypeName === 'int') {
                // https://github.com/php/php-src/blob/b7d90f09d4a1688f2692f2fa9067d0a07f78cc7d/ext/filter/logical_filters.c#L94
                // https://github.com/php/php-src/blob/b7d90f09d4a1688f2692f2fa9067d0a07f78cc7d/ext/filter/logical_filters.c#L197
                $value = filter_var($value, FILTER_VALIDATE_INT, FILTER_NULL_ON_FAILURE);
            }
        }

        if ($enumTypeName === 'int' && !is_int($value)) {
            throw new UnexpectedValueException('This value must be of type integer.');
        }
        if ($enumTypeName === 'string' && !is_string($value)) {
            throw new UnexpectedValueException('This value must be of type string.');
        }

        /** @var int|string $value */

        try {
            yield $enumName::from($value);
        } catch (ValueError $e) {
            $choices = [...$this->getEnumChoices($enumName)];

            throw new UnexpectedValueException(sprintf(
                'This value must be one of: %s.',
                join(', ', $choices),
            ), previous: $e);
        }
    }

    /**
     * Gets choices from the given enum
     *
     * @param class-string<BackedEnum> $enumName
     *
     * @return Generator<int, int|string>
     */
    private function getEnumChoices(string $enumName): Generator
    {
        /** @var list<BackedEnum> $cases */
        $cases = $enumName::cases();

        foreach ($cases as $case) {
            yield $case->value;
        }
    }
}
