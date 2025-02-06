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
        );

        $this->assertSame('foo, bar!', $violation->getMessage());
        $this->assertSame('foo, {bar}!', $violation->getMessageTemplate());
        $this->assertSame(['{bar}' => 'bar'], $violation->getMessagePlaceholders());
        $this->assertSame('foo.bar', $violation->getPropertyPath());
        $this->assertSame('287272ea-e485-405e-aab4-c5e522daaa78', $violation->getCode());
        $this->assertSame(0, $violation->getInvalidValue());
    }
}
