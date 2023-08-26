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

use function is_string;

/**
 * @since 3.0.0
 */
final class StringTypeConverter implements TypeConverterInterface
{

    /**
     * @inheritDoc
     *
     * @throws InvalidArgumentException If the value isn't valid.
     */
    public function castValue(mixed $value, ReflectionType $type): Generator
    {
        if (! $type instanceof ReflectionNamedType || $type->getName() !== 'string') {
            return;
        }

        if (!is_string($value)) {
            throw new InvalidArgumentException('This value must be of type string.');
        }

        yield $value;
    }
}
