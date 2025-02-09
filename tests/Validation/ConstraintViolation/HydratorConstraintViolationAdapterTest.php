<?php

declare(strict_types=1);

namespace Sunrise\Http\Router\Tests\Validation\ConstraintViolation;

use PHPUnit\Framework\TestCase;
use Sunrise\Http\Router\Validation\ConstraintViolation\HydratorConstraintViolationAdapter;
use Sunrise\Hydrator\Exception\InvalidValueException;

final class HydratorConstraintViolationAdapterTest extends TestCase
{
    public function testAdapter(): void
    {
        $adaptedViolation = new HydratorConstraintViolationAdapter(
            new InvalidValueException(
                message: 'foo, bar!',
                errorCode: '287272ea-e485-405e-aab4-c5e522daaa78',
                propertyPath: ['foo', 0, 'bar'],
                messageTemplate: 'foo, {bar}!',
                messagePlaceholders: ['{bar}' => 'bar'],
                invalidValue: 0,
            )
        );

        self::assertSame('foo, bar!', $adaptedViolation->getMessage());
        self::assertSame('foo, {bar}!', $adaptedViolation->getMessageTemplate());
        self::assertSame(['{bar}' => 'bar'], $adaptedViolation->getMessagePlaceholders());
        self::assertSame('foo.0.bar', $adaptedViolation->getPropertyPath());
        self::assertSame('287272ea-e485-405e-aab4-c5e522daaa78', $adaptedViolation->getCode());
        self::assertSame(0, $adaptedViolation->getInvalidValue());
    }
}
