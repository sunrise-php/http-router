<?php

declare(strict_types=1);

namespace Sunrise\Http\Router\Tests\ParameterResolver;

use InvalidArgumentException;
use JsonSerializable;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ServerRequestInterface;
use ReflectionParameter;
use Sunrise\Http\Router\Annotation\RequestQuery;
use Sunrise\Http\Router\Dictionary\ErrorMessage;
use Sunrise\Http\Router\Exception\HttpException;
use Sunrise\Http\Router\ParameterResolver\RequestQueryParameterResolver;
use Sunrise\Http\Router\Tests\Fixture\App\Dto\Common\PaginationDto;
use Sunrise\Http\Router\Tests\Fixture\App\Dto\Page\PageFilterRequest;
use Sunrise\Http\Router\Tests\Fixture\App\Dto\Page\PageListRequest;
use Sunrise\Hydrator\Exception\InvalidDataException;
use Sunrise\Hydrator\Exception\InvalidValueException;
use Sunrise\Hydrator\HydratorInterface;
use Symfony\Component\Validator\ConstraintViolationInterface;
use Symfony\Component\Validator\ConstraintViolationList;
use Symfony\Component\Validator\Validator\ValidatorInterface;

final class RequestQueryParameterResolverTest extends TestCase
{
    private HydratorInterface&MockObject $mockedHydrator;
    private ValidatorInterface&MockObject $mockedValidator;
    private ServerRequestInterface&MockObject $mockedServerRequest;

    protected function setUp(): void
    {
        $this->mockedHydrator = $this->createMock(HydratorInterface::class);
        $this->mockedValidator = $this->createMock(ValidatorInterface::class);
        $this->mockedServerRequest = $this->createMock(ServerRequestInterface::class);
    }

    public function testResolveParameter(): void
    {
        $pageListRequest = new PageListRequest(filter: new PageFilterRequest(name: 'foo'));
        $this->mockedServerRequest->expects(self::once())->method('getQueryParams')->willReturn(['filter' => ['name' => 'foo']]);
        $this->mockedHydrator->expects(self::once())->method('hydrate')->with(PageListRequest::class, ['filter' => ['name' => 'foo']])->willReturn($pageListRequest);
        $parameter = new ReflectionParameter(fn(#[RequestQuery] PageListRequest $p) => null, 'p');
        $arguments = (new RequestQueryParameterResolver($this->mockedHydrator))->resolveParameter($parameter, $this->mockedServerRequest);
        $this->assertSame($pageListRequest, $arguments->current());
    }

    public function testUnsupportedContext(): void
    {
        $this->mockedHydrator->expects(self::never())->method('hydrate');
        $parameter = new ReflectionParameter(fn(#[RequestQuery] PageListRequest $p) => null, 'p');
        $arguments = (new RequestQueryParameterResolver($this->mockedHydrator))->resolveParameter($parameter, null);
        $this->assertFalse($arguments->valid());
    }

    public function testNonAnnotatedParameter(): void
    {
        $this->mockedHydrator->expects(self::never())->method('hydrate');
        $parameter = new ReflectionParameter(fn(PageListRequest $p) => null, 'p');
        $arguments = (new RequestQueryParameterResolver($this->mockedHydrator))->resolveParameter($parameter, $this->mockedServerRequest);
        $this->assertFalse($arguments->valid());
    }

    public function testNonNamedParameterType(): void
    {
        $this->mockedHydrator->expects(self::never())->method('hydrate');
        $parameter = new ReflectionParameter(fn(#[RequestQuery] PageListRequest&JsonSerializable $p) => null, 'p');
        $arguments = (new RequestQueryParameterResolver($this->mockedHydrator))->resolveParameter($parameter, $this->mockedServerRequest);
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessageMatches('/must be typed with an object/');
        $arguments->rewind();
    }

    public function testBuiltInParameterType(): void
    {
        $this->mockedHydrator->expects(self::never())->method('hydrate');
        $parameter = new ReflectionParameter(fn(#[RequestQuery] object $p) => null, 'p');
        $arguments = (new RequestQueryParameterResolver($this->mockedHydrator))->resolveParameter($parameter, $this->mockedServerRequest);
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessageMatches('/must be typed with an object/');
        $arguments->rewind();
    }

    public function testHydratorError(): void
    {
        $this->mockedServerRequest->expects(self::once())->method('getQueryParams')->willReturn(['filter' => null]);
        $invalidValueException = InvalidValueException::mustBeArray(['filter']);
        $invalidDataException = new InvalidDataException('Invalid data', [$invalidValueException]);
        $this->mockedHydrator->expects(self::once())->method('hydrate')->with(PageListRequest::class)->willThrowException($invalidDataException);
        $parameter = new ReflectionParameter(fn(#[RequestQuery] PageListRequest $p) => null, 'p');
        $arguments = (new RequestQueryParameterResolver($this->mockedHydrator))->resolveParameter($parameter, $this->mockedServerRequest);
        $this->expectException(HttpException::class);
        $this->expectExceptionMessage(ErrorMessage::INVALID_QUERY);

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
        $pageListRequest = new PageListRequest(pagination: new PaginationDto(limit: 0));
        $this->mockedServerRequest->expects(self::once())->method('getQueryParams')->willReturn(['pagination' => ['limit' => 0]]);
        $this->mockedHydrator->expects(self::once())->method('hydrate')->with(PageListRequest::class, ['pagination' => ['limit' => 0]])->willReturn($pageListRequest);
        $constraintViolation = $this->createMock(ConstraintViolationInterface::class);
        $constraintViolation->method('getMessage')->willReturn('pagination limit is invalid');
        $constraintViolation->method('getPropertyPath')->willReturn('pagination.limit');
        $constraintViolation->method('getCode')->willReturn('332628a6-5f6c-4cbf-9d92-69f1c81ba4d9');
        $constraintViolation->method('getInvalidValue')->willReturn($pageListRequest->pagination->limit);
        $constraintViolationList = new ConstraintViolationList([$constraintViolation]);
        $this->mockedValidator->expects(self::once())->method('validate')->with($pageListRequest)->willReturn($constraintViolationList);
        $parameter = new ReflectionParameter(fn(#[RequestQuery] PageListRequest $p) => null, 'p');
        $arguments = (new RequestQueryParameterResolver($this->mockedHydrator, $this->mockedValidator))->resolveParameter($parameter, $this->mockedServerRequest);
        $this->expectException(HttpException::class);
        $this->expectExceptionMessage(ErrorMessage::INVALID_QUERY);

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
        $this->mockedServerRequest->expects(self::once())->method('getQueryParams')->willReturn([]);
        $this->mockedHydrator->expects(self::once())->method('hydrate')->with(PageListRequest::class)->willThrowException(new InvalidDataException('Invalid data'));
        $parameter = new ReflectionParameter(fn(#[RequestQuery] PageListRequest $p) => null, 'p');
        $arguments = (new RequestQueryParameterResolver($this->mockedHydrator, defaultErrorStatusCode: 500))->resolveParameter($parameter, $this->mockedServerRequest);

        try {
            $arguments->rewind();
        } catch (HttpException $e) {
            $this->assertSame(500, $e->getCode());
        }
    }

    public function testErrorStatusCodeFromAnnotation(): void
    {
        $this->mockedServerRequest->expects(self::once())->method('getQueryParams')->willReturn([]);
        $this->mockedHydrator->expects(self::once())->method('hydrate')->with(PageListRequest::class)->willThrowException(new InvalidDataException('Invalid data'));
        $parameter = new ReflectionParameter(fn(#[RequestQuery(errorStatusCode: 503)] PageListRequest $p) => null, 'p');
        $arguments = (new RequestQueryParameterResolver($this->mockedHydrator, defaultErrorStatusCode: 500))->resolveParameter($parameter, $this->mockedServerRequest);

        try {
            $arguments->rewind();
        } catch (HttpException $e) {
            $this->assertSame(503, $e->getCode());
        }
    }

    public function testDefaultErrorMessage(): void
    {
        $this->mockedServerRequest->expects(self::once())->method('getQueryParams')->willReturn([]);
        $this->mockedHydrator->expects(self::once())->method('hydrate')->with(PageListRequest::class)->willThrowException(new InvalidDataException('Invalid data'));
        $parameter = new ReflectionParameter(fn(#[RequestQuery] PageListRequest $p) => null, 'p');
        $arguments = (new RequestQueryParameterResolver($this->mockedHydrator, defaultErrorMessage: 'foo'))->resolveParameter($parameter, $this->mockedServerRequest);

        try {
            $arguments->rewind();
        } catch (HttpException $e) {
            $this->assertSame('foo', $e->getMessage());
        }
    }

    public function testErrorMessageFromAnnotation(): void
    {
        $this->mockedServerRequest->expects(self::once())->method('getQueryParams')->willReturn([]);
        $this->mockedHydrator->expects(self::once())->method('hydrate')->with(PageListRequest::class)->willThrowException(new InvalidDataException('Invalid data'));
        $parameter = new ReflectionParameter(fn(#[RequestQuery(errorMessage: 'bar')] PageListRequest $p) => null, 'p');
        $arguments = (new RequestQueryParameterResolver($this->mockedHydrator, defaultErrorMessage: 'foo'))->resolveParameter($parameter, $this->mockedServerRequest);

        try {
            $arguments->rewind();
        } catch (HttpException $e) {
            $this->assertSame('bar', $e->getMessage());
        }
    }

    public function testHydratorContext(): void
    {
        $pageListRequest = new PageListRequest();
        $this->mockedServerRequest->expects(self::once())->method('getQueryParams')->willReturn(['name' => 'foo']);
        $this->mockedHydrator->expects(self::once())->method('hydrate')->with(PageListRequest::class, ['name' => 'foo'], [], ['foo' => 'baz', 'baz' => 'qux', 'bar' => 'baz'])->willReturn($pageListRequest);
        $parameter = new ReflectionParameter(fn(#[RequestQuery(hydratorContext: ['foo' => 'baz', 'baz' => 'qux'])] PageListRequest $p) => null, 'p');
        (new RequestQueryParameterResolver($this->mockedHydrator, hydratorContext: ['foo' => 'bar', 'bar' => 'baz']))->resolveParameter($parameter, $this->mockedServerRequest)->rewind();
    }

    public function testDisableValidationByDefault(): void
    {
        $pageListRequest = new PageListRequest();
        $this->mockedServerRequest->expects(self::once())->method('getQueryParams')->willReturn(['name' => 'foo']);
        $this->mockedHydrator->expects(self::once())->method('hydrate')->with(PageListRequest::class, ['name' => 'foo'])->willReturn($pageListRequest);
        $this->mockedValidator->expects(self::never())->method('validate');
        $parameter = new ReflectionParameter(fn(#[RequestQuery] PageListRequest $p) => null, 'p');
        (new RequestQueryParameterResolver($this->mockedHydrator, $this->mockedValidator, defaultValidationEnabled: false))->resolveParameter($parameter, $this->mockedServerRequest)->rewind();
    }

    public function testDisableValidationFromAnnotation(): void
    {
        $pageListRequest = new PageListRequest();
        $this->mockedServerRequest->expects(self::once())->method('getQueryParams')->willReturn(['name' => 'foo']);
        $this->mockedHydrator->expects(self::once())->method('hydrate')->with(PageListRequest::class, ['name' => 'foo'])->willReturn($pageListRequest);
        $this->mockedValidator->expects(self::never())->method('validate');
        $parameter = new ReflectionParameter(fn(#[RequestQuery(validationEnabled: false)] PageListRequest $p) => null, 'p');
        (new RequestQueryParameterResolver($this->mockedHydrator, $this->mockedValidator))->resolveParameter($parameter, $this->mockedServerRequest)->rewind();
    }

    public function testEnableValidationFromAnnotation(): void
    {
        $pageListRequest = new PageListRequest();
        $this->mockedServerRequest->expects(self::once())->method('getQueryParams')->willReturn(['name' => 'foo']);
        $this->mockedHydrator->expects(self::once())->method('hydrate')->with(PageListRequest::class, ['name' => 'foo'])->willReturn($pageListRequest);
        $this->mockedValidator->expects(self::once())->method('validate')->with($pageListRequest);
        $parameter = new ReflectionParameter(fn(#[RequestQuery(validationEnabled: true)] PageListRequest $p) => null, 'p');
        (new RequestQueryParameterResolver($this->mockedHydrator, $this->mockedValidator, defaultValidationEnabled: false))->resolveParameter($parameter, $this->mockedServerRequest)->rewind();
    }

    public function testWeight(): void
    {
        $this->assertSame(0, (new RequestQueryParameterResolver($this->mockedHydrator))->getWeight());
    }
}
