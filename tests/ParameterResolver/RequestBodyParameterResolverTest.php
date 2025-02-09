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
use Sunrise\Http\Router\Tests\TestKit;
use Sunrise\Hydrator\Exception\InvalidDataException;
use Sunrise\Hydrator\Exception\InvalidValueException;
use Sunrise\Hydrator\HydratorInterface;
use Symfony\Component\Validator\ConstraintViolationList;
use Symfony\Component\Validator\Validator\ValidatorInterface;

final class RequestBodyParameterResolverTest extends TestCase
{
    use TestKit;

    private HydratorInterface&MockObject $mockedHydrator;
    private ValidatorInterface&MockObject $mockedValidator;
    private ServerRequestInterface&MockObject $mockedRequest;

    protected function setUp(): void
    {
        $this->mockedHydrator = $this->createMock(HydratorInterface::class);
        $this->mockedValidator = $this->createMock(ValidatorInterface::class);
        $this->mockedRequest = $this->createMock(ServerRequestInterface::class);
    }

    public function testResolveParameter(): void
    {
        $pageCreateRequest = new PageCreateRequest(name: 'foo');
        $this->mockedRequest->expects(self::once())->method('getParsedBody')->willReturn(['name' => 'foo']);
        $this->mockedHydrator->expects(self::once())->method('hydrate')->with(PageCreateRequest::class, ['name' => 'foo'])->willReturn($pageCreateRequest);
        $parameter = new ReflectionParameter(fn(#[RequestBody] PageCreateRequest $p) => null, 'p');
        $arguments = (new RequestBodyParameterResolver($this->mockedHydrator))->resolveParameter($parameter, $this->mockedRequest);
        self::assertSame($pageCreateRequest, $arguments->current());
    }

    public function testUnsupportedContext(): void
    {
        $this->mockedHydrator->expects(self::never())->method('hydrate');
        $parameter = new ReflectionParameter(fn(#[RequestBody] PageCreateRequest $p) => null, 'p');
        $arguments = (new RequestBodyParameterResolver($this->mockedHydrator))->resolveParameter($parameter, null);
        self::assertFalse($arguments->valid());
    }

    public function testNonAnnotatedParameter(): void
    {
        $this->mockedHydrator->expects(self::never())->method('hydrate');
        $parameter = new ReflectionParameter(fn(PageCreateRequest $p) => null, 'p');
        $arguments = (new RequestBodyParameterResolver($this->mockedHydrator))->resolveParameter($parameter, $this->mockedRequest);
        self::assertFalse($arguments->valid());
    }

    public function testNonNamedParameterType(): void
    {
        $this->mockedHydrator->expects(self::never())->method('hydrate');
        $parameter = new ReflectionParameter(fn(#[RequestBody] PageCreateRequest&JsonSerializable $p) => null, 'p');
        $arguments = (new RequestBodyParameterResolver($this->mockedHydrator))->resolveParameter($parameter, $this->mockedRequest);
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessageMatches('/must be typed with an object/');
        $arguments->rewind();
    }

    public function testBuiltInParameterType(): void
    {
        $this->mockedHydrator->expects(self::never())->method('hydrate');
        $parameter = new ReflectionParameter(fn(#[RequestBody] object $p) => null, 'p');
        $arguments = (new RequestBodyParameterResolver($this->mockedHydrator))->resolveParameter($parameter, $this->mockedRequest);
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessageMatches('/must be typed with an object/');
        $arguments->rewind();
    }

    public function testHydratorError(): void
    {
        $this->mockedRequest->expects(self::once())->method('getParsedBody')->willReturn(['name' => null]);
        $invalidValueException = InvalidValueException::mustBeString(['name']);
        $invalidDataException = new InvalidDataException('Invalid data', [$invalidValueException]);
        $this->mockedHydrator->expects(self::once())->method('hydrate')->with(PageCreateRequest::class)->willThrowException($invalidDataException);
        $parameter = new ReflectionParameter(fn(#[RequestBody] PageCreateRequest $p) => null, 'p');
        $arguments = (new RequestBodyParameterResolver($this->mockedHydrator))->resolveParameter($parameter, $this->mockedRequest);
        $this->expectException(HttpException::class);
        $this->expectExceptionMessage(ErrorMessage::INVALID_BODY);

        try {
            $arguments->rewind();
        } catch (HttpException $e) {
            self::assertSame(400, $e->getCode());
            $violations = $e->getConstraintViolations();
            self::assertArrayHasKey(0, $violations);
            self::assertSame($invalidValueException->getMessage(), $violations[0]->getMessage());
            self::assertSame($invalidValueException->getPropertyPath(), $violations[0]->getPropertyPath());
            self::assertSame($invalidValueException->getErrorCode(), $violations[0]->getCode());
            self::assertSame($invalidValueException->getInvalidValue(), $violations[0]->getInvalidValue());
            throw $e;
        }
    }

    public function testValidatorError(): void
    {
        $pageCreateRequest = new PageCreateRequest(name: 'foo');
        $this->mockedRequest->expects(self::once())->method('getParsedBody')->willReturn(['name' => 'foo']);
        $this->mockedHydrator->expects(self::once())->method('hydrate')->with(PageCreateRequest::class, ['name' => 'foo'])->willReturn($pageCreateRequest);
        $constraintViolation = $this->mockValidatorConstraintViolation(message: 'name is invalid', propertyPath: 'name', code: 'eb32bcd3-c254-4a96-b2b2-41dd2d4b3c22', invalidValue: $pageCreateRequest->name);
        $this->mockedValidator->expects(self::once())->method('validate')->with($pageCreateRequest)->willReturn(new ConstraintViolationList([$constraintViolation]));
        $parameter = new ReflectionParameter(fn(#[RequestBody] PageCreateRequest $p) => null, 'p');
        $arguments = (new RequestBodyParameterResolver($this->mockedHydrator, $this->mockedValidator))->resolveParameter($parameter, $this->mockedRequest);
        $this->expectException(HttpException::class);
        $this->expectExceptionMessage(ErrorMessage::INVALID_BODY);

        try {
            $arguments->rewind();
        } catch (HttpException $e) {
            self::assertSame(400, $e->getCode());
            $violations = $e->getConstraintViolations();
            self::assertArrayHasKey(0, $violations);
            self::assertSame($constraintViolation->getMessage(), $violations[0]->getMessage());
            self::assertSame($constraintViolation->getPropertyPath(), $violations[0]->getPropertyPath());
            self::assertSame($constraintViolation->getCode(), $violations[0]->getCode());
            self::assertSame($constraintViolation->getInvalidValue(), $violations[0]->getInvalidValue());
            throw $e;
        }
    }

    public function testDefaultErrorStatusCode(): void
    {
        $this->mockedRequest->expects(self::once())->method('getParsedBody')->willReturn([]);
        $this->mockedHydrator->expects(self::once())->method('hydrate')->with(PageCreateRequest::class)->willThrowException(new InvalidDataException('Invalid data'));
        $parameter = new ReflectionParameter(fn(#[RequestBody] PageCreateRequest $p) => null, 'p');
        $arguments = (new RequestBodyParameterResolver($this->mockedHydrator, defaultErrorStatusCode: 500))->resolveParameter($parameter, $this->mockedRequest);

        try {
            $arguments->rewind();
        } catch (HttpException $e) {
            self::assertSame(500, $e->getCode());
        }
    }

    public function testErrorStatusCodeFromAnnotation(): void
    {
        $this->mockedRequest->expects(self::once())->method('getParsedBody')->willReturn([]);
        $this->mockedHydrator->expects(self::once())->method('hydrate')->with(PageCreateRequest::class)->willThrowException(new InvalidDataException('Invalid data'));
        $parameter = new ReflectionParameter(fn(#[RequestBody(errorStatusCode: 503)] PageCreateRequest $p) => null, 'p');
        $arguments = (new RequestBodyParameterResolver($this->mockedHydrator, defaultErrorStatusCode: 500))->resolveParameter($parameter, $this->mockedRequest);

        try {
            $arguments->rewind();
        } catch (HttpException $e) {
            self::assertSame(503, $e->getCode());
        }
    }

    public function testDefaultErrorMessage(): void
    {
        $this->mockedRequest->expects(self::once())->method('getParsedBody')->willReturn([]);
        $this->mockedHydrator->expects(self::once())->method('hydrate')->with(PageCreateRequest::class)->willThrowException(new InvalidDataException('Invalid data'));
        $parameter = new ReflectionParameter(fn(#[RequestBody] PageCreateRequest $p) => null, 'p');
        $arguments = (new RequestBodyParameterResolver($this->mockedHydrator, defaultErrorMessage: 'foo'))->resolveParameter($parameter, $this->mockedRequest);

        try {
            $arguments->rewind();
        } catch (HttpException $e) {
            self::assertSame('foo', $e->getMessage());
        }
    }

    public function testErrorMessageFromAnnotation(): void
    {
        $this->mockedRequest->expects(self::once())->method('getParsedBody')->willReturn([]);
        $this->mockedHydrator->expects(self::once())->method('hydrate')->with(PageCreateRequest::class)->willThrowException(new InvalidDataException('Invalid data'));
        $parameter = new ReflectionParameter(fn(#[RequestBody(errorMessage: 'bar')] PageCreateRequest $p) => null, 'p');
        $arguments = (new RequestBodyParameterResolver($this->mockedHydrator, defaultErrorMessage: 'foo'))->resolveParameter($parameter, $this->mockedRequest);

        try {
            $arguments->rewind();
        } catch (HttpException $e) {
            self::assertSame('bar', $e->getMessage());
        }
    }

    public function testHydratorContext(): void
    {
        $pageCreateRequest = new PageCreateRequest(name: 'foo');
        $this->mockedRequest->expects(self::once())->method('getParsedBody')->willReturn(['name' => 'foo']);
        $this->mockedHydrator->expects(self::once())->method('hydrate')->with(PageCreateRequest::class, ['name' => 'foo'], [], ['foo' => 'baz', 'baz' => 'qux', 'bar' => 'baz'])->willReturn($pageCreateRequest);
        $parameter = new ReflectionParameter(fn(#[RequestBody(hydratorContext: ['foo' => 'baz', 'baz' => 'qux'])] PageCreateRequest $p) => null, 'p');
        (new RequestBodyParameterResolver($this->mockedHydrator, hydratorContext: ['foo' => 'bar', 'bar' => 'baz']))->resolveParameter($parameter, $this->mockedRequest)->rewind();
    }

    public function testDisableValidationByDefault(): void
    {
        $pageCreateRequest = new PageCreateRequest(name: 'foo');
        $this->mockedRequest->expects(self::once())->method('getParsedBody')->willReturn(['name' => 'foo']);
        $this->mockedHydrator->expects(self::once())->method('hydrate')->with(PageCreateRequest::class, ['name' => 'foo'])->willReturn($pageCreateRequest);
        $this->mockedValidator->expects(self::never())->method('validate');
        $parameter = new ReflectionParameter(fn(#[RequestBody] PageCreateRequest $p) => null, 'p');
        (new RequestBodyParameterResolver($this->mockedHydrator, $this->mockedValidator, defaultValidationEnabled: false))->resolveParameter($parameter, $this->mockedRequest)->rewind();
    }

    public function testDisableValidationFromAnnotation(): void
    {
        $pageCreateRequest = new PageCreateRequest(name: 'foo');
        $this->mockedRequest->expects(self::once())->method('getParsedBody')->willReturn(['name' => 'foo']);
        $this->mockedHydrator->expects(self::once())->method('hydrate')->with(PageCreateRequest::class, ['name' => 'foo'])->willReturn($pageCreateRequest);
        $this->mockedValidator->expects(self::never())->method('validate');
        $parameter = new ReflectionParameter(fn(#[RequestBody(validationEnabled: false)] PageCreateRequest $p) => null, 'p');
        (new RequestBodyParameterResolver($this->mockedHydrator, $this->mockedValidator))->resolveParameter($parameter, $this->mockedRequest)->rewind();
    }

    public function testEnableValidationFromAnnotation(): void
    {
        $pageCreateRequest = new PageCreateRequest(name: 'foo');
        $this->mockedRequest->expects(self::once())->method('getParsedBody')->willReturn(['name' => 'foo']);
        $this->mockedHydrator->expects(self::once())->method('hydrate')->with(PageCreateRequest::class, ['name' => 'foo'])->willReturn($pageCreateRequest);
        $this->mockedValidator->expects(self::once())->method('validate')->with($pageCreateRequest);
        $parameter = new ReflectionParameter(fn(#[RequestBody(validationEnabled: true)] PageCreateRequest $p) => null, 'p');
        (new RequestBodyParameterResolver($this->mockedHydrator, $this->mockedValidator, defaultValidationEnabled: false))->resolveParameter($parameter, $this->mockedRequest)->rewind();
    }

    public function testWeight(): void
    {
        self::assertSame(0, (new RequestBodyParameterResolver($this->mockedHydrator))->getWeight());
    }
}
