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

use Generator;
use ReflectionType;
use Sunrise\Http\Router\Exception\LogicException;
use Sunrise\Http\Router\TypeConversion\TypeConverter\BackedEnumTypeConverter;
use Sunrise\Http\Router\TypeConversion\TypeConverter\BoolTypeConverter;
use Sunrise\Http\Router\TypeConversion\TypeConverter\TimestampTypeConverter;
use Sunrise\Http\Router\TypeConversion\TypeConverter\FloatTypeConverter;
use Sunrise\Http\Router\TypeConversion\TypeConverter\IntTypeConverter;
use Sunrise\Http\Router\TypeConversion\TypeConverter\StringTypeConverter;
use Sunrise\Http\Router\TypeConversion\TypeConverter\TypeConverterInterface;
use Sunrise\Http\Router\TypeConversion\TypeConverter\UidTypeConverter;

use function get_debug_type;
use function sprintf;

/**
 * @since 3.0.0
 */
final class TypeConversioner implements TypeConversionerInterface
{

    /**
     * @var list<TypeConverterInterface>
     */
    private array $converters = [];

    /**
     * @inheritDoc
     */
    public function addConverter(TypeConverterInterface ...$converters): void
    {
        foreach ($converters as $converter) {
            $this->converters[] = $converter;
        }
    }

    /**
     * @inheritDoc
     */
    public function castValue(mixed $value, ReflectionType $type): mixed
    {
        foreach ($this->converters as $converter) {
            $result = $converter->castValue($value, $type);
            if ($result->valid()) {
                return $result->current();
            }
        }

        throw new LogicException(sprintf(
            'The value {%s} cannot be converted to the type {%s} because it is not supported.',
            get_debug_type($value),
            (string) $type,
        ));
    }

    /**
     * Returns default type converters
     *
     * @return Generator<TypeConverterInterface>
     */
    public static function defaultConverters(): Generator
    {
        yield new BoolTypeConverter();
        yield new IntTypeConverter();
        yield new FloatTypeConverter();
        yield new StringTypeConverter();
        yield new BackedEnumTypeConverter();
        yield new TimestampTypeConverter();
        yield new UidTypeConverter();
    }
}
