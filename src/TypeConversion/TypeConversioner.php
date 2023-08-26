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

use ReflectionType;
use Sunrise\Http\Router\Exception\LogicException;
use Sunrise\Http\Router\TypeConversion\TypeConverter\TypeConverterInterface;

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
     *
     * @throws LogicException If the type isn't supported or cannot be applied to the value.
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
            'Unable to convert the value {%s} to the type %s.',
            get_debug_type($value),
            (string) $type,
        ));
    }
}
