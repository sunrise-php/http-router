<?php

declare(strict_types=1);

namespace Sunrise\Http\Router\Tests\Exception;

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Stringable;
use Sunrise\Http\Router\Exception\HttpException;
use Sunrise\Http\Router\Validation\ConstraintViolationInterface;
use Throwable;

final class HttpExceptionTest extends TestCase
{
    public function testConstructor(): void
    {
        $previous = $this->createMock(Throwable::class);
        $httpException = new HttpException('foo', 400, $previous);
        $this->assertSame('foo', $httpException->getMessage());
        $this->assertSame(400, $httpException->getCode());
        $this->assertSame($previous, $httpException->getPrevious());
        $this->assertSame('foo', $httpException->getMessageTemplate());
        $this->assertSame([], $httpException->getMessagePlaceholders());
        $this->assertSame([], $httpException->getHeaderFields());
        $this->assertSame([], $httpException->getConstraintViolations());
    }

    public function testConstructorWithoutOptionalParameters(): void
    {
        $httpException = new HttpException('foo', 400);
        $this->assertSame('foo', $httpException->getMessage());
        $this->assertSame(400, $httpException->getCode());
        $this->assertNull($httpException->getPrevious());
        $this->assertSame('foo', $httpException->getMessageTemplate());
        $this->assertSame([], $httpException->getMessagePlaceholders());
        $this->assertSame([], $httpException->getHeaderFields());
        $this->assertSame([], $httpException->getConstraintViolations());
    }

    public function testAddMessagePlaceholder(): void
    {
    }

    public function testAddHeaderFieldWithString(): void
    {
        $httpException = new HttpException('foo', 400);

        $httpException->addHeaderField('x-foo', 'bar', 'baz');
        $httpException->addHeaderField('x-foo', 'qux');
        $httpException->addHeaderField('x-foo');

        $this->assertSame([
            ['x-foo', 'bar, baz'],
            ['x-foo', 'qux'],
            ['x-foo', ''],
        ], $httpException->getHeaderFields());
    }

    public function testAddHeaderFieldWithStringableObject(): void
    {
        $httpException = new HttpException('foo', 400);
        $httpException->addHeaderField('x-foo', $this->mockStringableObject('bar'));
        $this->assertSame([['x-foo', 'bar']], $httpException->getHeaderFields());
    }

    public function testAddConstraintViolation(): void
    {
        $httpException = new HttpException('foo', 400);
        $constraintViolations = [];
        $constraintViolations[] = $this->createMock(ConstraintViolationInterface::class);
        $constraintViolations[] = $this->createMock(ConstraintViolationInterface::class);
        $httpException->addConstraintViolation(...$constraintViolations);
        $additionalConstraintViolation = $this->createMock(ConstraintViolationInterface::class);
        $httpException->addConstraintViolation($additionalConstraintViolation);
        $expectedConstraintViolations = [...$constraintViolations, $additionalConstraintViolation];
        $this->assertSame($expectedConstraintViolations, $httpException->getConstraintViolations());
    }

    public function testAddHydratorConstraintViolations(): void
    {
        /**
         * @var array{
         *        0: \Sunrise\Hydrator\Exception\InvalidValueException,
         *        1: \Sunrise\Hydrator\Exception\InvalidValueException
         *      } $hydratorConstraintViolations
         */
        $hydratorConstraintViolations = [
            $this->createHydratorConstraintViolation(
                message: 'foo {bar}',
                messageTemplate: 'foo {bar}',
                messagePlaceholders: ['{bar}' => 'bar'],
                propertyPath: ['foo', 'bar'],
                errorCode: '00000000-0000-0000-0000-000000000000',
                invalidValue: ['bar' => null],
            ),
            $this->createHydratorConstraintViolation(
                message: 'bar {baz}',
                messageTemplate: 'bar {baz}',
                messagePlaceholders: ['{baz}' => 'baz'],
                propertyPath: ['bar', 'baz'],
                errorCode: '00000000-0000-0000-0000-000000000001',
                invalidValue: ['baz' => null],
            ),
        ];

        $additionalHydratorConstraintViolation = $this->createHydratorConstraintViolation(
            message: 'baz {qux}',
            messageTemplate: 'baz {qux}',
            messagePlaceholders: ['{qux}' => 'qux'],
            propertyPath: ['baz', 'qux'],
            errorCode: '00000000-0000-0000-0000-000000000002',
            invalidValue: ['qux' => null],
        );

        $httpException = new HttpException('foo', 400);
        $httpException->addHydratorConstraintViolation(...$hydratorConstraintViolations);
        $httpException->addHydratorConstraintViolation($additionalHydratorConstraintViolation);
        $constraintViolations = $httpException->getConstraintViolations();
        $this->assertCount(3, $constraintViolations);

        $this->assertArrayHasKey(0, $constraintViolations);
        $this->assertSame($hydratorConstraintViolations[0]->getMessage(), $constraintViolations[0]->getMessage());
        $this->assertSame($hydratorConstraintViolations[0]->getMessageTemplate(), $constraintViolations[0]->getMessageTemplate());
        $this->assertSame($hydratorConstraintViolations[0]->getMessagePlaceholders(), $constraintViolations[0]->getMessagePlaceholders());
        $this->assertSame($hydratorConstraintViolations[0]->getPropertyPath(), $constraintViolations[0]->getPropertyPath());
        $this->assertSame($hydratorConstraintViolations[0]->getErrorCode(), $constraintViolations[0]->getCode());
        $this->assertSame($hydratorConstraintViolations[0]->getInvalidValue(), $constraintViolations[0]->getInvalidValue());

        $this->assertArrayHasKey(1, $constraintViolations);
        $this->assertSame($hydratorConstraintViolations[1]->getMessage(), $constraintViolations[1]->getMessage());
        $this->assertSame($hydratorConstraintViolations[1]->getMessageTemplate(), $constraintViolations[1]->getMessageTemplate());
        $this->assertSame($hydratorConstraintViolations[1]->getMessagePlaceholders(), $constraintViolations[1]->getMessagePlaceholders());
        $this->assertSame($hydratorConstraintViolations[1]->getPropertyPath(), $constraintViolations[1]->getPropertyPath());
        $this->assertSame($hydratorConstraintViolations[1]->getErrorCode(), $constraintViolations[1]->getCode());
        $this->assertSame($hydratorConstraintViolations[1]->getInvalidValue(), $constraintViolations[1]->getInvalidValue());

        $this->assertArrayHasKey(2, $constraintViolations);
        $this->assertSame($additionalHydratorConstraintViolation->getMessage(), $constraintViolations[2]->getMessage());
        $this->assertSame($additionalHydratorConstraintViolation->getMessageTemplate(), $constraintViolations[2]->getMessageTemplate());
        $this->assertSame($additionalHydratorConstraintViolation->getMessagePlaceholders(), $constraintViolations[2]->getMessagePlaceholders());
        $this->assertSame($additionalHydratorConstraintViolation->getPropertyPath(), $constraintViolations[2]->getPropertyPath());
        $this->assertSame($additionalHydratorConstraintViolation->getErrorCode(), $constraintViolations[2]->getCode());
        $this->assertSame($additionalHydratorConstraintViolation->getInvalidValue(), $constraintViolations[2]->getInvalidValue());
    }

    public function testAddValidatorConstraintViolations(): void
    {
        /**
         * @var array{
         *        0: \Symfony\Component\Validator\ConstraintViolationInterface&MockObject,
         *        1: \Symfony\Component\Validator\ConstraintViolationInterface&MockObject
         *      } $validatorConstraintViolations
         */
        $validatorConstraintViolations = [
            $this->mockValidatorConstraintViolation(
                message: 'foo {bar}',
                messageTemplate: 'foo {bar}',
                messagePlaceholders: ['{bar}' => 'bar'],
                propertyPath: 'foo.bar',
                errorCode: '00000000-0000-0000-0000-000000000000',
                invalidValue: ['bar' => null],
            ),
            $this->mockValidatorConstraintViolation(
                message: 'bar {baz}',
                messageTemplate: 'bar {baz}',
                messagePlaceholders: ['{baz}' => 'baz'],
                propertyPath: 'bar.baz',
                errorCode: '00000000-0000-0000-0000-000000000001',
                invalidValue: ['baz' => null],
            ),
        ];

        $additionalValidatorConstraintViolation = $this->mockValidatorConstraintViolation(
            message: 'baz {qux}',
            messageTemplate: 'baz {qux}',
            messagePlaceholders: ['{qux}' => 'qux'],
            propertyPath: 'baz.qux',
            errorCode: '00000000-0000-0000-0000-000000000002',
            invalidValue: ['qux' => null],
        );

        $httpException = new HttpException('foo', 400);
        $httpException->addValidatorConstraintViolation(...$validatorConstraintViolations);
        $httpException->addValidatorConstraintViolation($additionalValidatorConstraintViolation);
        $constraintViolations = $httpException->getConstraintViolations();
        $this->assertCount(3, $constraintViolations);

        $this->assertArrayHasKey(0, $constraintViolations);
        $this->assertSame($validatorConstraintViolations[0]->getMessage(), $constraintViolations[0]->getMessage());
        $this->assertSame($validatorConstraintViolations[0]->getMessageTemplate(), $constraintViolations[0]->getMessageTemplate());
        $this->assertSame($validatorConstraintViolations[0]->getParameters(), $constraintViolations[0]->getMessagePlaceholders());
        $this->assertSame($validatorConstraintViolations[0]->getPropertyPath(), $constraintViolations[0]->getPropertyPath());
        $this->assertSame($validatorConstraintViolations[0]->getCode(), $constraintViolations[0]->getCode());
        $this->assertSame($validatorConstraintViolations[0]->getInvalidValue(), $constraintViolations[0]->getInvalidValue());

        $this->assertArrayHasKey(1, $constraintViolations);
        $this->assertSame($validatorConstraintViolations[1]->getMessage(), $constraintViolations[1]->getMessage());
        $this->assertSame($validatorConstraintViolations[1]->getMessageTemplate(), $constraintViolations[1]->getMessageTemplate());
        $this->assertSame($validatorConstraintViolations[1]->getParameters(), $constraintViolations[1]->getMessagePlaceholders());
        $this->assertSame($validatorConstraintViolations[1]->getPropertyPath(), $constraintViolations[1]->getPropertyPath());
        $this->assertSame($validatorConstraintViolations[1]->getCode(), $constraintViolations[1]->getCode());
        $this->assertSame($validatorConstraintViolations[1]->getInvalidValue(), $constraintViolations[1]->getInvalidValue());

        $this->assertArrayHasKey(2, $constraintViolations);
        $this->assertSame($additionalValidatorConstraintViolation->getMessage(), $constraintViolations[2]->getMessage());
        $this->assertSame($additionalValidatorConstraintViolation->getMessageTemplate(), $constraintViolations[2]->getMessageTemplate());
        $this->assertSame($additionalValidatorConstraintViolation->getParameters(), $constraintViolations[2]->getMessagePlaceholders());
        $this->assertSame($additionalValidatorConstraintViolation->getPropertyPath(), $constraintViolations[2]->getPropertyPath());
        $this->assertSame($additionalValidatorConstraintViolation->getCode(), $constraintViolations[2]->getCode());
        $this->assertSame($additionalValidatorConstraintViolation->getInvalidValue(), $constraintViolations[2]->getInvalidValue());
    }

    private function mockStringableObject(string $string): Stringable&MockObject
    {
        $stringableObjectMock = $this->createMock(Stringable::class);
        $stringableObjectMock->method('__toString')->willReturn($string);

        return $stringableObjectMock;
    }

    private function createHydratorConstraintViolation(
        string $message,
        string $messageTemplate,
        array $messagePlaceholders,
        array $propertyPath,
        string $errorCode,
        mixed $invalidValue = null,
    ): \Sunrise\Hydrator\Exception\InvalidValueException {
        return new \Sunrise\Hydrator\Exception\InvalidValueException(
            message: $message,
            errorCode: $errorCode,
            propertyPath: $propertyPath,
            messageTemplate: $messageTemplate,
            messagePlaceholders: $messagePlaceholders,
            invalidValue: $invalidValue,
        );
    }

    private function mockValidatorConstraintViolation(
        string $message,
        string $messageTemplate,
        array $messagePlaceholders,
        string $propertyPath,
        string $errorCode,
        mixed $invalidValue = null,
    ): \Symfony\Component\Validator\ConstraintViolationInterface&MockObject {
        $constraintViolationMock = $this->createMock(\Symfony\Component\Validator\ConstraintViolationInterface::class);
        $constraintViolationMock->method('getMessage')->willReturn($message);
        $constraintViolationMock->method('getMessageTemplate')->willReturn($messageTemplate);
        $constraintViolationMock->method('getParameters')->willReturn($messagePlaceholders);
        $constraintViolationMock->method('getPropertyPath')->willReturn($propertyPath);
        $constraintViolationMock->method('getCode')->willReturn($errorCode);
        $constraintViolationMock->method('getInvalidValue')->willReturn($invalidValue);

        return $constraintViolationMock;
    }
}
