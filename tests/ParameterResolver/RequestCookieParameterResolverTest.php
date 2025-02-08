<?php

declare(strict_types=1);

namespace Sunrise\Http\Router\Tests\ParameterResolver;

use Generator;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ServerRequestInterface;
use ReflectionParameter;
use Sunrise\Http\Router\Annotation\Constraint;
use Sunrise\Http\Router\Annotation\RequestCookie;
use Sunrise\Http\Router\Exception\HttpException;
use Sunrise\Http\Router\ParameterResolver\RequestCookieParameterResolver;
use Sunrise\Hydrator\Exception\InvalidDataException;
use Sunrise\Hydrator\Exception\InvalidValueException;
use Sunrise\Hydrator\HydratorInterface;
use Symfony\Component\Validator\ConstraintViolationInterface;
use Symfony\Component\Validator\ConstraintViolationList;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Validator\ContextualValidatorInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

final class RequestCookieParameterResolverTest extends TestCase
{
    private HydratorInterface&MockObject $mockedHydrator;
    private ValidatorInterface&MockObject $mockedValidator;
    private ContextualValidatorInterface&MockObject $mockedContextualValidator;
    private ServerRequestInterface&MockObject $mockedServerRequest;

    protected function setUp(): void
    {
        $this->mockedHydrator = $this->createMock(HydratorInterface::class);
        $this->mockedValidator = $this->createMock(ValidatorInterface::class);
        $this->mockedContextualValidator = $this->createMock(ContextualValidatorInterface::class);
        $this->mockedValidator->method('startContext')->willReturn($this->mockedContextualValidator);
        $this->mockedServerRequest = $this->createMock(ServerRequestInterface::class);
    }

    public function testResolveParameter(): void
    {
        $this->mockedServerRequest->expects(self::once())->method('getCookieParams')->willReturn(['foo' => 'bar']);
        $this->mockedHydrator->expects(self::once())->method('castValue')->with('bar')->willReturn('bar');
        $parameter = new ReflectionParameter(fn(#[RequestCookie('foo')] string $p) => null, 'p');
        $arguments = (new RequestCookieParameterResolver($this->mockedHydrator))->resolveParameter($parameter, $this->mockedServerRequest);
        $this->assertSame('bar', $arguments->current());
    }

    public function testUnsupportedContext(): void
    {
        $this->mockedHydrator->expects(self::never())->method('castValue');
        $parameter = new ReflectionParameter(fn(#[RequestCookie('foo')] string $p) => null, 'p');
        $arguments = (new RequestCookieParameterResolver($this->mockedHydrator))->resolveParameter($parameter, null);
        $this->assertFalse($arguments->valid());
    }

    public function testNonAnnotatedParameter(): void
    {
        $this->mockedHydrator->expects(self::never())->method('castValue');
        $parameter = new ReflectionParameter(fn(string $p) => null, 'p');
        $arguments = (new RequestCookieParameterResolver($this->mockedHydrator))->resolveParameter($parameter, $this->mockedServerRequest);
        $this->assertFalse($arguments->valid());
    }

    public function testDefaultParameterValue(): void
    {
        $this->mockedServerRequest->expects(self::once())->method('getCookieParams')->willReturn([]);
        $this->mockedHydrator->expects(self::never())->method('castValue');
        $parameter = new ReflectionParameter(fn(#[RequestCookie('foo')] string $p = 'bar') => null, 'p');
        $arguments = (new RequestCookieParameterResolver($this->mockedHydrator))->resolveParameter($parameter, $this->mockedServerRequest);
        $this->assertSame('bar', $arguments->current());
    }

    public function testMissingCookie(): void
    {
        $this->mockedServerRequest->expects(self::once())->method('getCookieParams')->willReturn([]);
        $this->mockedHydrator->expects(self::never())->method('castValue');
        $parameter = new ReflectionParameter(fn(#[RequestCookie('foo')] string $p) => null, 'p');
        $arguments = (new RequestCookieParameterResolver($this->mockedHydrator))->resolveParameter($parameter, $this->mockedServerRequest);
        $this->expectException(HttpException::class);

        try {
            $arguments->rewind();
        } catch (HttpException $e) {
            $this->assertSame(400, $e->getCode());
            throw $e;
        }
    }

    #[DataProvider('hydratorErrorDataProvider')]
    public function testHydratorError(InvalidDataException|InvalidValueException $hydratorError): void
    {
        $this->mockedServerRequest->expects(self::once())->method('getCookieParams')->willReturn(['foo' => ['bar']]);
        $this->mockedHydrator->expects(self::once())->method('castValue')->with(['bar'])->willThrowException($hydratorError);
        $parameter = new ReflectionParameter(fn(#[RequestCookie('foo')] string $p) => null, 'p');
        $arguments = (new RequestCookieParameterResolver($this->mockedHydrator))->resolveParameter($parameter, $this->mockedServerRequest);
        $this->expectException(HttpException::class);

        $hydratorViolations = $hydratorError instanceof InvalidValueException ? [$hydratorError] : $hydratorError->getExceptions();

        try {
            $arguments->rewind();
        } catch (HttpException $e) {
            $this->assertSame(400, $e->getCode());
            $violations = $e->getConstraintViolations();
            $this->assertArrayHasKey(0, $violations);
            $this->assertSame($hydratorViolations[0]->getMessage(), $violations[0]->getMessage());
            $this->assertSame($hydratorViolations[0]->getPropertyPath(), $violations[0]->getPropertyPath());
            $this->assertSame($hydratorViolations[0]->getErrorCode(), $violations[0]->getCode());
            $this->assertSame($hydratorViolations[0]->getInvalidValue(), $violations[0]->getInvalidValue());
            throw $e;
        }
    }

    public static function hydratorErrorDataProvider(): Generator
    {
        yield [
            new InvalidDataException('Invalid data', [
                InvalidValueException::mustBeString(['foo']),
            ]),
        ];

        yield [
            InvalidValueException::mustBeString(['foo']),
        ];
    }

    public function testValidatorError(): void
    {
        $this->mockedServerRequest->expects(self::once())->method('getCookieParams')->willReturn(['foo' => '']);
        $this->mockedHydrator->expects(self::once())->method('castValue')->with('')->willReturn('');
        $validatorViolation = $this->createMock(ConstraintViolationInterface::class);
        $validatorViolation->method('getMessage')->willReturn('This value should not be blank.');
        $validatorViolation->method('getPropertyPath')->willReturn('foo');
        $validatorViolation->method('getCode')->willReturn(NotBlank::IS_BLANK_ERROR);
        $validatorViolation->method('getInvalidValue')->willReturn('');
        $validatorViolations = new ConstraintViolationList([$validatorViolation]);
        $this->mockedContextualValidator->expects(self::once())->method('atPath')->with('foo')->willReturn($this->mockedContextualValidator);
        $this->mockedContextualValidator->expects(self::once())->method('validate')->with('')->willReturn($this->mockedContextualValidator);
        $this->mockedContextualValidator->expects(self::once())->method('getViolations')->willReturn($validatorViolations);
        $parameter = new ReflectionParameter(fn(#[RequestCookie('foo'), Constraint(new NotBlank())] string $p) => null, 'p');
        $arguments = (new RequestCookieParameterResolver($this->mockedHydrator, $this->mockedValidator))->resolveParameter($parameter, $this->mockedServerRequest);
        $this->expectException(HttpException::class);

        try {
            $arguments->rewind();
        } catch (HttpException $e) {
            $this->assertSame(400, $e->getCode());
            $violations = $e->getConstraintViolations();
            $this->assertArrayHasKey(0, $violations);
            $this->assertSame($validatorViolations[0]->getMessage(), $violations[0]->getMessage());
            $this->assertSame($validatorViolations[0]->getPropertyPath(), $violations[0]->getPropertyPath());
            $this->assertSame($validatorViolations[0]->getCode(), $violations[0]->getCode());
            $this->assertSame($validatorViolations[0]->getInvalidValue(), $violations[0]->getInvalidValue());
            throw $e;
        }
    }

    public function testDefaultErrorStatusCode(): void
    {
        $this->mockedServerRequest->expects(self::once())->method('getCookieParams')->willReturn(['foo' => ['bar']]);
        $this->mockedHydrator->expects(self::once())->method('castValue')->with(['bar'])->willThrowException(InvalidValueException::mustBeString(['foo']));
        $parameter = new ReflectionParameter(fn(#[RequestCookie('foo')] string $p) => null, 'p');
        $arguments = (new RequestCookieParameterResolver($this->mockedHydrator, defaultErrorStatusCode: 500))->resolveParameter($parameter, $this->mockedServerRequest);

        try {
            $arguments->rewind();
        } catch (HttpException $e) {
            $this->assertSame(500, $e->getCode());
        }
    }

    public function testErrorStatusCodeFromAnnotation(): void
    {
        $this->mockedServerRequest->expects(self::once())->method('getCookieParams')->willReturn(['foo' => ['bar']]);
        $this->mockedHydrator->expects(self::once())->method('castValue')->with(['bar'])->willThrowException(InvalidValueException::mustBeString(['foo']));
        $parameter = new ReflectionParameter(fn(#[RequestCookie('foo', errorStatusCode: 503)] string $p) => null, 'p');
        $arguments = (new RequestCookieParameterResolver($this->mockedHydrator, defaultErrorStatusCode: 500))->resolveParameter($parameter, $this->mockedServerRequest);

        try {
            $arguments->rewind();
        } catch (HttpException $e) {
            $this->assertSame(503, $e->getCode());
        }
    }

    public function testDefaultErrorMessage(): void
    {
        $this->mockedServerRequest->expects(self::once())->method('getCookieParams')->willReturn(['foo' => ['bar']]);
        $this->mockedHydrator->expects(self::once())->method('castValue')->with(['bar'])->willThrowException(InvalidValueException::mustBeString(['foo']));
        $parameter = new ReflectionParameter(fn(#[RequestCookie('foo')] string $p) => null, 'p');
        $arguments = (new RequestCookieParameterResolver($this->mockedHydrator, defaultErrorMessage: 'foo'))->resolveParameter($parameter, $this->mockedServerRequest);

        try {
            $arguments->rewind();
        } catch (HttpException $e) {
            $this->assertSame('foo', $e->getMessage());
        }
    }

    public function testErrorMessageFromAnnotation(): void
    {
        $this->mockedServerRequest->expects(self::once())->method('getCookieParams')->willReturn(['foo' => ['bar']]);
        $this->mockedHydrator->expects(self::once())->method('castValue')->with(['bar'])->willThrowException(InvalidValueException::mustBeString(['foo']));
        $parameter = new ReflectionParameter(fn(#[RequestCookie('foo', errorMessage: 'bar')] string $p) => null, 'p');
        $arguments = (new RequestCookieParameterResolver($this->mockedHydrator, defaultErrorMessage: 'foo'))->resolveParameter($parameter, $this->mockedServerRequest);

        try {
            $arguments->rewind();
        } catch (HttpException $e) {
            $this->assertSame('bar', $e->getMessage());
        }
    }

    public function testHydratorContext(): void
    {
        $this->mockedServerRequest->expects(self::once())->method('getCookieParams')->willReturn(['foo' => 'bar']);
        $this->mockedHydrator->expects(self::once())->method('castValue')->with('bar', self::anything(), self::anything(), ['foo' => 'baz', 'baz' => 'qux', 'bar' => 'baz'])->willReturn('bar');
        $parameter = new ReflectionParameter(fn(#[RequestCookie('foo', hydratorContext: ['foo' => 'baz', 'baz' => 'qux'])] string $p) => null, 'p');
        (new RequestCookieParameterResolver($this->mockedHydrator, hydratorContext: ['foo' => 'bar', 'bar' => 'baz']))->resolveParameter($parameter, $this->mockedServerRequest)->rewind();
    }

    public function testDisableValidationByDefault(): void
    {
        $this->mockedServerRequest->expects(self::once())->method('getCookieParams')->willReturn(['foo' => 'bar']);
        $this->mockedHydrator->expects(self::once())->method('castValue')->with('bar')->willReturn('bar');
        $this->mockedValidator->expects(self::never())->method('validate');
        $parameter = new ReflectionParameter(fn(#[RequestCookie('foo')] string $p) => null, 'p');
        (new RequestCookieParameterResolver($this->mockedHydrator, $this->mockedValidator, defaultValidationEnabled: false))->resolveParameter($parameter, $this->mockedServerRequest)->rewind();
    }

    public function testDisableValidationFromAnnotation(): void
    {
        $this->mockedServerRequest->expects(self::once())->method('getCookieParams')->willReturn(['foo' => 'bar']);
        $this->mockedHydrator->expects(self::once())->method('castValue')->with('bar')->willReturn('bar');
        $this->mockedValidator->expects(self::never())->method('validate');
        $parameter = new ReflectionParameter(fn(#[RequestCookie('foo', validationEnabled: false)] string $p) => null, 'p');
        (new RequestCookieParameterResolver($this->mockedHydrator, $this->mockedValidator))->resolveParameter($parameter, $this->mockedServerRequest)->rewind();
    }

    public function testEnableValidationFromAnnotation(): void
    {
        $this->mockedServerRequest->expects(self::once())->method('getCookieParams')->willReturn(['foo' => 'bar']);
        $this->mockedHydrator->expects(self::once())->method('castValue')->with('bar')->willReturn('bar');
        $this->mockedContextualValidator->expects(self::once())->method('atPath')->with('foo')->willReturn($this->mockedContextualValidator);
        $this->mockedContextualValidator->expects(self::once())->method('validate')->with('bar')->willReturn($this->mockedContextualValidator);
        $this->mockedContextualValidator->expects(self::once())->method('getViolations');
        $parameter = new ReflectionParameter(fn(#[RequestCookie('foo', validationEnabled: true)] string $p) => null, 'p');
        (new RequestCookieParameterResolver($this->mockedHydrator, $this->mockedValidator, defaultValidationEnabled: false))->resolveParameter($parameter, $this->mockedServerRequest)->rewind();
    }

    public function testWeight(): void
    {
        $this->assertSame(0, (new RequestCookieParameterResolver($this->mockedHydrator))->getWeight());
    }
}
