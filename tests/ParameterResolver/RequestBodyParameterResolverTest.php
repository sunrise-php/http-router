<?php

declare(strict_types=1);

namespace Sunrise\Http\Router\Tests\ParameterResolver;

use InvalidArgumentException;
use JsonSerializable;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ServerRequestInterface;
use ReflectionParameter;
use Sunrise\Http\Router\Annotation\RequestBody;
use Sunrise\Http\Router\Dictionary\ErrorMessage;
use Sunrise\Http\Router\Exception\HttpException;
use Sunrise\Http\Router\ParameterResolver\RequestBodyParameterResolver;
use Sunrise\Http\Router\Tests\Fixture\App\Dto\Page\PageCreateRequest;
use Sunrise\Hydrator\Exception\InvalidDataException;
use Sunrise\Hydrator\Exception\InvalidValueException;
use Sunrise\Hydrator\HydratorInterface;
use Symfony\Component\Validator\ConstraintViolationInterface;
use Symfony\Component\Validator\ConstraintViolationList;
use Symfony\Component\Validator\Validator\ValidatorInterface;

final class RequestBodyParameterResolverTest extends TestCase
{
    private ServerRequestInterface&MockObject $mockedServerRequest;
    private HydratorInterface&MockObject $mockedHydrator;
    private ValidatorInterface&MockObject $mockedValidator;

    protected function setUp(): void
    {
        $this->mockedServerRequest = $this->createMock(ServerRequestInterface::class);
        $this->mockedHydrator = $this->createMock(HydratorInterface::class);
        $this->mockedValidator = $this->createMock(ValidatorInterface::class);
    }

    public function testResolveParameter(): void
    {
        $pageCreateRequest = new PageCreateRequest(name: 'foo');
        $this->mockedServerRequest->expects(self::once())->method('getParsedBody')->willReturn(['name' => 'foo']);
        $this->mockedHydrator->expects(self::once())->method('hydrate')->with(PageCreateRequest::class, ['name' => 'foo'])->willReturn($pageCreateRequest);
        $parameter = new ReflectionParameter(fn(#[RequestBody] PageCreateRequest $p) => null, 'p');
        $arguments = (new RequestBodyParameterResolver($this->mockedHydrator))->resolveParameter($parameter, $this->mockedServerRequest);
        $this->assertSame($pageCreateRequest, $arguments->current());
    }

    public function testUnsupportedContext(): void
    {
        $parameter = new ReflectionParameter(fn(#[RequestBody] PageCreateRequest $p) => null, 'p');
        $arguments = (new RequestBodyParameterResolver($this->mockedHydrator))->resolveParameter($parameter, null);
        $this->assertFalse($arguments->valid());
    }

    public function testNonAnnotatedParameter(): void
    {
        $parameter = new ReflectionParameter(fn(PageCreateRequest $p) => null, 'p');
        $arguments = (new RequestBodyParameterResolver($this->mockedHydrator))->resolveParameter($parameter, $this->mockedServerRequest);
        $this->assertFalse($arguments->valid());
    }

    public function testNonNamedParameterType(): void
    {
        $parameter = new ReflectionParameter(fn(#[RequestBody] PageCreateRequest&JsonSerializable $p) => null, 'p');
        $arguments = (new RequestBodyParameterResolver($this->mockedHydrator))->resolveParameter($parameter, $this->mockedServerRequest);
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessageMatches('/must be typed with an object/');
        $arguments->rewind();
    }

    public function testBuiltInParameterType(): void
    {
        $parameter = new ReflectionParameter(fn(#[RequestBody] object $p) => null, 'p');
        $arguments = (new RequestBodyParameterResolver($this->mockedHydrator))->resolveParameter($parameter, $this->mockedServerRequest);
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessageMatches('/must be typed with an object/');
        $arguments->rewind();
    }

    public function testHydratorError(): void
    {
        $this->mockedServerRequest->expects(self::once())->method('getParsedBody')->willReturn(['name' => null]);
        $invalidValueException = InvalidValueException::mustBeString(['name']);
        $invalidDataException = new InvalidDataException('Invalid data', [$invalidValueException]);
        $this->mockedHydrator->expects(self::once())->method('hydrate')->with(PageCreateRequest::class)->willThrowException($invalidDataException);
        $parameter = new ReflectionParameter(fn(#[RequestBody] PageCreateRequest $p) => null, 'p');
        $arguments = (new RequestBodyParameterResolver($this->mockedHydrator))->resolveParameter($parameter, $this->mockedServerRequest);
        $this->expectException(HttpException::class);
        $this->expectExceptionMessage(ErrorMessage::INVALID_BODY);

        try {
            $arguments->rewind();
        } catch (HttpException $e) {
            $this->assertSame(400, $e->getCode());
            $violations = $e->getConstraintViolations();
            $this->assertArrayHasKey(0, $violations);
            $this->assertSame($invalidValueException->getMessage(), $violations[0]->getMessage());
            $this->assertSame($invalidValueException->getPropertyPath(), $violations[0]->getPropertyPath());
            $this->assertSame($invalidValueException->getErrorCode(), $violations[0]->getCode());
            $this->assertSame($invalidValueException->getInvalidValue(), $violations[0]->getInvalidValue());
            throw $e;
        }
    }

    public function testValidatorError(): void
    {
        $pageCreateRequest = new PageCreateRequest(name: 'foo');
        $this->mockedServerRequest->expects(self::once())->method('getParsedBody')->willReturn(['name' => 'foo']);
        $this->mockedHydrator->expects(self::once())->method('hydrate')->with(PageCreateRequest::class, ['name' => 'foo'])->willReturn($pageCreateRequest);
        $constraintViolation = $this->createMock(ConstraintViolationInterface::class);
        $constraintViolation->method('getMessage')->willReturn('name is invalid');
        $constraintViolation->method('getPropertyPath')->willReturn('name');
        $constraintViolation->method('getCode')->willReturn('eb32bcd3-c254-4a96-b2b2-41dd2d4b3c22');
        $constraintViolation->method('getInvalidValue')->willReturn($pageCreateRequest->name);
        $constraintViolationList = new ConstraintViolationList([$constraintViolation]);
        $this->mockedValidator->expects(self::once())->method('validate')->with($pageCreateRequest)->willReturn($constraintViolationList);
        $parameter = new ReflectionParameter(fn(#[RequestBody] PageCreateRequest $p) => null, 'p');
        $arguments = (new RequestBodyParameterResolver($this->mockedHydrator, $this->mockedValidator))->resolveParameter($parameter, $this->mockedServerRequest);
        $this->expectException(HttpException::class);
        $this->expectExceptionMessage(ErrorMessage::INVALID_BODY);

        try {
            $arguments->rewind();
        } catch (HttpException $e) {
            $this->assertSame(400, $e->getCode());
            $violations = $e->getConstraintViolations();
            $this->assertArrayHasKey(0, $violations);
            $this->assertSame($constraintViolation->getMessage(), $violations[0]->getMessage());
            $this->assertSame($constraintViolation->getPropertyPath(), $violations[0]->getPropertyPath());
            $this->assertSame($constraintViolation->getCode(), $violations[0]->getCode());
            $this->assertSame($constraintViolation->getInvalidValue(), $violations[0]->getInvalidValue());
            throw $e;
        }
    }

    public function testDefaultErrorStatusCode(): void
    {
        $this->mockedServerRequest->expects(self::once())->method('getParsedBody')->willReturn([]);
        $this->mockedHydrator->expects(self::once())->method('hydrate')->with(PageCreateRequest::class)->willThrowException(new InvalidDataException('Invalid data'));
        $parameter = new ReflectionParameter(fn(#[RequestBody] PageCreateRequest $p) => null, 'p');
        $arguments = (new RequestBodyParameterResolver($this->mockedHydrator, defaultErrorStatusCode: 500))->resolveParameter($parameter, $this->mockedServerRequest);

        try {
            $arguments->rewind();
        } catch (HttpException $e) {
            $this->assertSame(500, $e->getCode());
        }
    }

    public function testErrorStatusCodeFromAnnotation(): void
    {
        $this->mockedServerRequest->expects(self::once())->method('getParsedBody')->willReturn([]);
        $this->mockedHydrator->expects(self::once())->method('hydrate')->with(PageCreateRequest::class)->willThrowException(new InvalidDataException('Invalid data'));
        $parameter = new ReflectionParameter(fn(#[RequestBody(errorStatusCode: 503)] PageCreateRequest $p) => null, 'p');
        $arguments = (new RequestBodyParameterResolver($this->mockedHydrator, defaultErrorStatusCode: 500))->resolveParameter($parameter, $this->mockedServerRequest);

        try {
            $arguments->rewind();
        } catch (HttpException $e) {
            $this->assertSame(503, $e->getCode());
        }
    }

    public function testDefaultErrorMessage(): void
    {
        $this->mockedServerRequest->expects(self::once())->method('getParsedBody')->willReturn([]);
        $this->mockedHydrator->expects(self::once())->method('hydrate')->with(PageCreateRequest::class)->willThrowException(new InvalidDataException('Invalid data'));
        $parameter = new ReflectionParameter(fn(#[RequestBody] PageCreateRequest $p) => null, 'p');
        $arguments = (new RequestBodyParameterResolver($this->mockedHydrator, defaultErrorMessage: 'foo'))->resolveParameter($parameter, $this->mockedServerRequest);

        try {
            $arguments->rewind();
        } catch (HttpException $e) {
            $this->assertSame('foo', $e->getMessage());
        }
    }

    public function testErrorMessageFromAnnotation(): void
    {
        $this->mockedServerRequest->expects(self::once())->method('getParsedBody')->willReturn([]);
        $this->mockedHydrator->expects(self::once())->method('hydrate')->with(PageCreateRequest::class)->willThrowException(new InvalidDataException('Invalid data'));
        $parameter = new ReflectionParameter(fn(#[RequestBody(errorMessage: 'bar')] PageCreateRequest $p) => null, 'p');
        $arguments = (new RequestBodyParameterResolver($this->mockedHydrator, defaultErrorMessage: 'foo'))->resolveParameter($parameter, $this->mockedServerRequest);

        try {
            $arguments->rewind();
        } catch (HttpException $e) {
            $this->assertSame('bar', $e->getMessage());
        }
    }

    public function testHydratorContext(): void
    {
        $pageCreateRequest = new PageCreateRequest(name: 'foo');
        $this->mockedServerRequest->expects(self::once())->method('getParsedBody')->willReturn(['name' => 'foo']);
        $this->mockedHydrator->expects(self::once())->method('hydrate')->with(PageCreateRequest::class, ['name' => 'foo'], [], ['foo' => 'baz', 'baz' => 'qux', 'bar' => 'baz'])->willReturn($pageCreateRequest);
        $parameter = new ReflectionParameter(fn(#[RequestBody(hydratorContext: ['foo' => 'baz', 'baz' => 'qux'])] PageCreateRequest $p) => null, 'p');
        (new RequestBodyParameterResolver($this->mockedHydrator, hydratorContext: ['foo' => 'bar', 'bar' => 'baz']))->resolveParameter($parameter, $this->mockedServerRequest)->rewind();
    }

    public function testDisableValidationByDefault(): void
    {
        $pageCreateRequest = new PageCreateRequest(name: 'foo');
        $this->mockedServerRequest->expects(self::once())->method('getParsedBody')->willReturn(['name' => 'foo']);
        $this->mockedHydrator->expects(self::once())->method('hydrate')->with(PageCreateRequest::class, ['name' => 'foo'])->willReturn($pageCreateRequest);
        $this->mockedValidator->expects(self::never())->method('validate');
        $parameter = new ReflectionParameter(fn(#[RequestBody] PageCreateRequest $p) => null, 'p');
        (new RequestBodyParameterResolver($this->mockedHydrator, $this->mockedValidator, defaultValidationEnabled: false))->resolveParameter($parameter, $this->mockedServerRequest)->rewind();
    }

    public function testDisableValidationFromAnnotation(): void
    {
        $pageCreateRequest = new PageCreateRequest(name: 'foo');
        $this->mockedServerRequest->expects(self::once())->method('getParsedBody')->willReturn(['name' => 'foo']);
        $this->mockedHydrator->expects(self::once())->method('hydrate')->with(PageCreateRequest::class, ['name' => 'foo'])->willReturn($pageCreateRequest);
        $this->mockedValidator->expects(self::never())->method('validate');
        $parameter = new ReflectionParameter(fn(#[RequestBody(validationEnabled: false)] PageCreateRequest $p) => null, 'p');
        (new RequestBodyParameterResolver($this->mockedHydrator, $this->mockedValidator))->resolveParameter($parameter, $this->mockedServerRequest)->rewind();
    }

    public function testEnableValidationFromAnnotation(): void
    {
        $pageCreateRequest = new PageCreateRequest(name: 'foo');
        $this->mockedServerRequest->expects(self::once())->method('getParsedBody')->willReturn(['name' => 'foo']);
        $this->mockedHydrator->expects(self::once())->method('hydrate')->with(PageCreateRequest::class, ['name' => 'foo'])->willReturn($pageCreateRequest);
        $this->mockedValidator->expects(self::once())->method('validate')->with($pageCreateRequest);
        $parameter = new ReflectionParameter(fn(#[RequestBody(validationEnabled: true)] PageCreateRequest $p) => null, 'p');
        (new RequestBodyParameterResolver($this->mockedHydrator, $this->mockedValidator, defaultValidationEnabled: false))->resolveParameter($parameter, $this->mockedServerRequest)->rewind();
    }
}
