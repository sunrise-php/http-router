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

namespace Sunrise\Http\Router\TypeConversion;

use LogicException;
use ReflectionType;
use Sunrise\Http\Router\TypeConversion\TypeConverter\TypeConverterInterface;
use UnexpectedValueException;

/**
 * @since 3.0.0
 */
interface TypeConversionerInterface
{

    /**
     * Adds the given type converter(s) to the conversioner
     *
     * @param TypeConverterInterface ...$converters
     *
     * @return void
     */
    public function addConverter(TypeConverterInterface ...$converters): void;

    /**
     * Trying to cast the given value to the given type
     *
     * @param mixed $value
     * @param ReflectionType $type
     *
     * @return mixed
     *
     * @throws LogicException If the type isn't supported.
     *
     * @throws UnexpectedValueException If the value isn't valid.
     */
    public function castValue(mixed $value, ReflectionType $type): mixed;
}
