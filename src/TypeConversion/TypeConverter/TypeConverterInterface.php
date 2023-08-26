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
use InvalidArgumentException;
use ReflectionType;

/**
 * @since 3.0.0
 */
interface TypeConverterInterface
{

    /**
     * Tries to cast the given value to the given type
     *
     * @param mixed $value
     * @param ReflectionType $type
     *
     * @return Generator<mixed>
     *
     * @throws InvalidArgumentException If the value isn't valid.
     */
    public function castValue(mixed $value, ReflectionType $type): Generator;
}
