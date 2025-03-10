<?php

declare(strict_types=1);

namespace Sunrise\Http\Router\Tests\Validation;

use PHPUnit\Framework\TestCase;
use Sunrise\Http\Router\Validation\ConstraintViolation;

final class ConstraintViolationTest extends TestCase
{
    public function testConstruct(): void
    {
        $violation = new ConstraintViolation(
            message: 'foo, bar!',
            messageTemplate: 'foo, {bar}!',
            messagePlaceholders: ['{bar}' => 'bar'],
            propertyPath: 'foo.bar',
            code: '287272ea-e485-405e-aab4-c5e522daaa78',
            invalidValue: 0,
            translationDomain: 'test',
        );

        self::assertSame('foo, bar!', $violation->getMessage());
        self::assertSame('foo, {bar}!', $violation->getMessageTemplate());
        self::assertSame(['{bar}' => 'bar'], $violation->getMessagePlaceholders());
        self::assertSame('foo.bar', $violation->getPropertyPath());
        self::assertSame('287272ea-e485-405e-aab4-c5e522daaa78', $violation->getCode());
        self::assertSame(0, $violation->getInvalidValue());
        self::assertSame('test', $violation->getTranslationDomain());
    }
}
