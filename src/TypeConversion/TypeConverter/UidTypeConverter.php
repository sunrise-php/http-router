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
use ReflectionNamedType;
use ReflectionType;
use Symfony\Component\Uid\AbstractUid;
use UnexpectedValueException;

use function is_string;
use function is_subclass_of;

/**
 * @link https://github.com/symfony/uid
 *
 * @since 3.0.0
 */
final class UidTypeConverter implements TypeConverterInterface
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

        if (!is_subclass_of($className, AbstractUid::class)) {
            return;
        }

        if (!is_string($value)) {
            throw new UnexpectedValueException('This value must be of type string.');
        }

        try {
            yield $className::fromString($value);
        } catch (InvalidArgumentException $e) {
            throw new UnexpectedValueException('This value is not a valid UID.', previous: $e);
        }
    }
}
