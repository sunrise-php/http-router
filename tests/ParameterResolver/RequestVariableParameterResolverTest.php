<?php

declare(strict_types=1);

namespace Sunrise\Http\Router\Tests\ParameterResolver;

use Generator;
use InvalidArgumentException;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ServerRequestInterface;
use ReflectionParameter;
use Sunrise\Http\Router\Annotation\Constraint;
use Sunrise\Http\Router\Annotation\RequestVariable;
use Sunrise\Http\Router\Exception\HttpException;
use Sunrise\Http\Router\ParameterResolver\RequestVariableParameterResolver;
use Sunrise\Http\Router\RouteInterface;
use Sunrise\Http\Router\Tests\TestKit;
use Sunrise\Hydrator\Exception\InvalidDataException;
use Sunrise\Hydrator\Exception\InvalidValueException;
use Sunrise\Hydrator\HydratorInterface;
use Symfony\Component\Validator\ConstraintViolationList;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Validator\ContextualValidatorInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

final class RequestVariableParameterResolverTest extends TestCase
{
    use TestKit;

    private HydratorInterface&MockObject $mockedHydrator;
    private ValidatorInterface&MockObject $mockedValidator;
    private ContextualValidatorInterface&MockObject $mockedContextualValidator;
    private ServerRequestInterface&MockObject $mockedRequest;
    private RouteInterface&MockObject $mockedRoute;

    protected function setUp(): void
    {
        $this->mockedHydrator = $this->createMock(HydratorInterface::class);
        $this->mockedValidator = $this->createMock(ValidatorInterface::class);
        $this->mockedContextualValidator = $this->createMock(ContextualValidatorInterface::class);
        $this->mockedValidator->expects(self::any())->method('startContext')->willReturn($this->mockedContextualValidator);
        $this->mockedRequest = $this->createMock(ServerRequestInterface::class);
        $this->mockedRoute = $this->createMock(RouteInterface::class);
        $this->mockedRequest->expects(self::any())->method('getAttribute')->with(RouteInterface::class)->willReturn($this->mockedRoute);
    }

    public function testResolveParameter(): void
    {
        $this->mockedRoute->expects(self::exactly(1))->method('hasAttribute')->with('foo')->willReturn(true);
        $this->mockedRoute->expects(self::exactly(1))->method('getAttribute')->with('foo')->willReturn('bar');
        $this->mockedHydrator->expects(self::once())->method('castValue')->with('bar')->willReturn('bar');
        $parameter = new ReflectionParameter(fn(#[RequestVariable('foo')] string $p) => null, 'p');
        $arguments = (new RequestVariableParameterResolver($this->mockedHydrator))->resolveParameter($parameter, $this->mockedRequest);
        self::assertSame('bar', $arguments->current());
    }

    public function testUnsupportedContext(): void
    {
        $this->mockedHydrator->expects(self::never())->method('castValue');
        $parameter = new ReflectionParameter(fn(#[RequestVariable('foo')] string $p) => null, 'p');
        $arguments = (new RequestVariableParameterResolver($this->mockedHydrator))->resolveParameter($parameter, null);
        self::assertFalse($arguments->valid());
    }

    public function testNonAnnotatedParameter(): void
    {
        $this->mockedHydrator->expects(self::never())->method('castValue');
        $parameter = new ReflectionParameter(fn(string $p) => null, 'p');
        $arguments = (new RequestVariableParameterResolver($this->mockedHydrator))->resolveParameter($parameter, $this->mockedRequest);
        self::assertFalse($arguments->valid());
    }

    public function testDefaultParameterValue(): void
    {
        $this->mockedRoute->expects(self::exactly(1))->method('hasAttribute')->with('foo')->willReturn(false);
        $this->mockedHydrator->expects(self::never())->method('castValue');
        $parameter = new ReflectionParameter(fn(#[RequestVariable('foo')] string $p = 'bar') => null, 'p');
        $arguments = (new RequestVariableParameterResolver($this->mockedHydrator))->resolveParameter($parameter, $this->mockedRequest);
        self::assertSame('bar', $arguments->current());
    }

    public function testMissingVariable(): void
    {
        $this->mockedRoute->expects(self::exactly(1))->method('hasAttribute')->with('foo')->willReturn(false);
        $this->mockedHydrator->expects(self::never())->method('castValue');
        $parameter = new ReflectionParameter(fn(#[RequestVariable('foo')] string $p) => null, 'p');
        $arguments = (new RequestVariableParameterResolver($this->mockedHydrator))->resolveParameter($parameter, $this->mockedRequest);
        $this->expectException(InvalidArgumentException::class);
        $arguments->valid();
    }

    #[DataProvider('hydratorErrorDataProvider')]
    public function testHydratorError(InvalidDataException|InvalidValueException $hydratorError): void
    {
        $this->mockedRoute->expects(self::exactly(1))->method('hasAttribute')->with('foo')->willReturn(true);
        $this->mockedRoute->expects(self::exactly(1))->method('getAttribute')->with('foo')->willReturn('bar');
        $this->mockedHydrator->expects(self::once())->method('castValue')->with('bar')->willThrowException($hydratorError);
        $parameter = new ReflectionParameter(fn(#[RequestVariable('foo')] int $p) => null, 'p');
        $arguments = (new RequestVariableParameterResolver($this->mockedHydrator))->resolveParameter($parameter, $this->mockedRequest);
        $this->expectException(HttpException::class);

        $hydratorViolations = $hydratorError instanceof InvalidValueException ? [$hydratorError] : $hydratorError->getExceptions();

        try {
            $arguments->valid();
        } catch (HttpException $e) {
            self::assertSame(400, $e->getCode());
            $violations = $e->getConstraintViolations();
            self::assertArrayHasKey(0, $violations);
            self::assertSame($hydratorViolations[0]->getMessage(), $violations[0]->getMessage());
            self::assertSame($hydratorViolations[0]->getPropertyPath(), $violations[0]->getPropertyPath());
            self::assertSame($hydratorViolations[0]->getErrorCode(), $violations[0]->getCode());
            self::assertSame($hydratorViolations[0]->getInvalidValue(), $violations[0]->getInvalidValue());
            throw $e;
        }
    }

    public static function hydratorErrorDataProvider(): Generator
    {
        yield [
            new InvalidDataException('Invalid data', [
                InvalidValueException::mustBeInteger(['foo']),
            ]),
        ];

        yield [
            InvalidValueException::mustBeInteger(['foo']),
        ];
    }

    public function testValidatorError(): void
    {
        $this->mockedRoute->expects(self::exactly(1))->method('hasAttribute')->with('foo')->willReturn(true);
        $this->mockedRoute->expects(self::exactly(1))->method('getAttribute')->with('foo')->willReturn('');
        $this->mockedHydrator->expects(self::once())->method('castValue')->with('')->willReturn('');
        $validatorViolation = $this->mockValidatorConstraintViolation(message: 'This value should not be blank.', propertyPath: 'foo', code: NotBlank::IS_BLANK_ERROR, invalidValue: '');
        $this->mockedContextualValidator->expects(self::once())->method('atPath')->with('foo')->willReturn($this->mockedContextualValidator);
        $this->mockedContextualValidator->expects(self::once())->method('validate')->with('')->willReturn($this->mockedContextualValidator);
        $this->mockedContextualValidator->expects(self::once())->method('getViolations')->willReturn(new ConstraintViolationList([$validatorViolation]));
        $parameter = new ReflectionParameter(fn(#[RequestVariable('foo'), Constraint(new NotBlank())] string $p) => null, 'p');
        $arguments = (new RequestVariableParameterResolver($this->mockedHydrator, $this->mockedValidator))->resolveParameter($parameter, $this->mockedRequest);
        $this->expectException(HttpException::class);

        try {
            $arguments->valid();
        } catch (HttpException $e) {
            self::assertSame(400, $e->getCode());
            $violations = $e->getConstraintViolations();
            self::assertArrayHasKey(0, $violations);
            self::assertSame($validatorViolation->getMessage(), $violations[0]->getMessage());
            self::assertSame($validatorViolation->getPropertyPath(), $violations[0]->getPropertyPath());
            self::assertSame($validatorViolation->getCode(), $violations[0]->getCode());
            self::assertSame($validatorViolation->getInvalidValue(), $violations[0]->getInvalidValue());
            throw $e;
        }
    }

    public function testDefaultErrorStatusCode(): void
    {
        $this->mockedRoute->expects(self::exactly(1))->method('hasAttribute')->with('foo')->willReturn(true);
        $this->mockedRoute->expects(self::exactly(1))->method('getAttribute')->with('foo')->willReturn('bar');
        $this->mockedHydrator->expects(self::once())->method('castValue')->with('bar')->willThrowException(InvalidValueException::mustBeInteger(['foo']));
        $parameter = new ReflectionParameter(fn(#[RequestVariable('foo')] int $p) => null, 'p');
        $arguments = (new RequestVariableParameterResolver($this->mockedHydrator, defaultErrorStatusCode: 500))->resolveParameter($parameter, $this->mockedRequest);

        try {
            $arguments->valid();
        } catch (HttpException $e) {
            self::assertSame(500, $e->getCode());
        }
    }

    public function testErrorStatusCodeFromAnnotation(): void
    {
        $this->mockedRoute->expects(self::exactly(1))->method('hasAttribute')->with('foo')->willReturn(true);
        $this->mockedRoute->expects(self::exactly(1))->method('getAttribute')->with('foo')->willReturn('bar');
        $this->mockedHydrator->expects(self::once())->method('castValue')->with('bar')->willThrowException(InvalidValueException::mustBeInteger(['foo']));
        $parameter = new ReflectionParameter(fn(#[RequestVariable('foo', errorStatusCode: 503)] int $p) => null, 'p');
        $arguments = (new RequestVariableParameterResolver($this->mockedHydrator, defaultErrorStatusCode: 500))->resolveParameter($parameter, $this->mockedRequest);

        try {
            $arguments->valid();
        } catch (HttpException $e) {
            self::assertSame(503, $e->getCode());
        }
    }

    public function testDefaultErrorMessage(): void
    {
        $this->mockedRoute->expects(self::exactly(1))->method('hasAttribute')->with('foo')->willReturn(true);
        $this->mockedRoute->expects(self::exactly(1))->method('getAttribute')->with('foo')->willReturn('bar');
        $this->mockedHydrator->expects(self::once())->method('castValue')->with('bar')->willThrowException(InvalidValueException::mustBeInteger(['foo']));
        $parameter = new ReflectionParameter(fn(#[RequestVariable('foo')] int $p) => null, 'p');
        $arguments = (new RequestVariableParameterResolver($this->mockedHydrator, defaultErrorMessage: 'foo'))->resolveParameter($parameter, $this->mockedRequest);

        try {
            $arguments->valid();
        } catch (HttpException $e) {
            self::assertSame('foo', $e->getMessage());
        }
    }

    public function testErrorMessageFromAnnotation(): void
    {
        $this->mockedRoute->expects(self::exactly(1))->method('hasAttribute')->with('foo')->willReturn(true);
        $this->mockedRoute->expects(self::exactly(1))->method('getAttribute')->with('foo')->willReturn('bar');
        $this->mockedHydrator->expects(self::once())->method('castValue')->with('bar')->willThrowException(InvalidValueException::mustBeInteger(['foo']));
        $parameter = new ReflectionParameter(fn(#[RequestVariable('foo', errorMessage: 'bar')] int $p) => null, 'p');
        $arguments = (new RequestVariableParameterResolver($this->mockedHydrator, defaultErrorMessage: 'foo'))->resolveParameter($parameter, $this->mockedRequest);

        try {
            $arguments->valid();
        } catch (HttpException $e) {
            self::assertSame('bar', $e->getMessage());
        }
    }

    public function testHydratorContext(): void
    {
        $this->mockedRoute->expects(self::exactly(1))->method('hasAttribute')->with('foo')->willReturn(true);
        $this->mockedRoute->expects(self::exactly(1))->method('getAttribute')->with('foo')->willReturn('bar');
        $this->mockedHydrator->expects(self::once())->method('castValue')->with('bar', self::anything(), self::anything(), ['foo' => 'baz', 'baz' => 'qux', 'bar' => 'baz'])->willReturn('bar');
        $parameter = new ReflectionParameter(fn(#[RequestVariable('foo', hydratorContext: ['foo' => 'baz', 'baz' => 'qux'])] string $p) => null, 'p');
        (new RequestVariableParameterResolver($this->mockedHydrator, hydratorContext: ['foo' => 'bar', 'bar' => 'baz']))->resolveParameter($parameter, $this->mockedRequest)->valid();
    }

    public function testDisableValidationByDefault(): void
    {
        $this->mockedRoute->expects(self::exactly(1))->method('hasAttribute')->with('foo')->willReturn(true);
        $this->mockedRoute->expects(self::exactly(1))->method('getAttribute')->with('foo')->willReturn('bar');
        $this->mockedHydrator->expects(self::once())->method('castValue')->with('bar')->willReturn('bar');
        $this->mockedValidator->expects(self::never())->method('validate');
        $parameter = new ReflectionParameter(fn(#[RequestVariable('foo')] string $p) => null, 'p');
        (new RequestVariableParameterResolver($this->mockedHydrator, $this->mockedValidator, defaultValidationEnabled: false))->resolveParameter($parameter, $this->mockedRequest)->valid();
    }

    public function testDisableValidationFromAnnotation(): void
    {
        $this->mockedRoute->expects(self::exactly(1))->method('hasAttribute')->with('foo')->willReturn(true);
        $this->mockedRoute->expects(self::exactly(1))->method('getAttribute')->with('foo')->willReturn('bar');
        $this->mockedHydrator->expects(self::once())->method('castValue')->with('bar')->willReturn('bar');
        $this->mockedValidator->expects(self::never())->method('validate');
        $parameter = new ReflectionParameter(fn(#[RequestVariable('foo', validationEnabled: false)] string $p) => null, 'p');
        (new RequestVariableParameterResolver($this->mockedHydrator, $this->mockedValidator))->resolveParameter($parameter, $this->mockedRequest)->valid();
    }

    public function testEnableValidationFromAnnotation(): void
    {
        $this->mockedRoute->expects(self::exactly(1))->method('hasAttribute')->with('foo')->willReturn(true);
        $this->mockedRoute->expects(self::exactly(1))->method('getAttribute')->with('foo')->willReturn('bar');
        $this->mockedHydrator->expects(self::once())->method('castValue')->with('bar')->willReturn('bar');
        $this->mockedContextualValidator->expects(self::once())->method('atPath')->with('foo')->willReturn($this->mockedContextualValidator);
        $this->mockedContextualValidator->expects(self::once())->method('validate')->with('bar')->willReturn($this->mockedContextualValidator);
        $this->mockedContextualValidator->expects(self::once())->method('getViolations');
        $parameter = new ReflectionParameter(fn(#[RequestVariable('foo', validationEnabled: true)] string $p) => null, 'p');
        (new RequestVariableParameterResolver($this->mockedHydrator, $this->mockedValidator, defaultValidationEnabled: false))->resolveParameter($parameter, $this->mockedRequest)->valid();
    }

    public function testWeight(): void
    {
        self::assertSame(0, (new RequestVariableParameterResolver($this->mockedHydrator))->getWeight());
    }
}
