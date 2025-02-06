<?php

declare(strict_types=1);

namespace Sunrise\Http\Router\Tests\Validation\ConstraintViolation;

use PHPUnit\Framework\TestCase;
use Sunrise\Http\Router\Validation\ConstraintViolation\ValidatorConstraintViolationAdapter;
use Symfony\Component\Validator\ConstraintViolationInterface;

final class ValidatorConstraintViolationAdapterTest extends TestCase
{
    public function testAdapter(): void
    {
        $validatorViolation = $this->createMock(ConstraintViolationInterface::class);
        $validatorViolation->expects(self::once())->method('getMessage')->willReturn('foo, bar!');
        $validatorViolation->expects(self::once())->method('getMessageTemplate')->willReturn('foo, {bar}!');
        $validatorViolation->expects(self::once())->method('getParameters')->willReturn(['{bar}' => 'bar']);
        $validatorViolation->expects(self::once())->method('getPropertyPath')->willReturn('foo[0].bar');
        $validatorViolation->expects(self::once())->method('getCode')->willReturn('287272ea-e485-405e-aab4-c5e522daaa78');
        $validatorViolation->expects(self::once())->method('getInvalidValue')->willReturn(0);

        $adaptedViolation = new ValidatorConstraintViolationAdapter($validatorViolation);

        $this->assertSame('foo, bar!', $adaptedViolation->getMessage());
        $this->assertSame('foo, {bar}!', $adaptedViolation->getMessageTemplate());
        $this->assertSame(['{bar}' => 'bar'], $adaptedViolation->getMessagePlaceholders());
        $this->assertSame('foo.0.bar', $adaptedViolation->getPropertyPath());
        $this->assertSame('287272ea-e485-405e-aab4-c5e522daaa78', $adaptedViolation->getCode());
        $this->assertSame(0, $adaptedViolation->getInvalidValue());
    }
}
