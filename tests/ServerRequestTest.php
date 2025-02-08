<?php

declare(strict_types=1);

namespace Sunrise\Http\Router\Tests;

use Generator;
use LogicException;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\StreamInterface;
use Psr\Http\Message\UploadedFileInterface;
use Psr\Http\Message\UriInterface;
use Sunrise\Http\Router\LocaleInterface;
use Sunrise\Http\Router\MediaTypeInterface;
use Sunrise\Http\Router\RouteInterface;
use Sunrise\Http\Router\ServerRequest;

use function array_map;

final class ServerRequestTest extends TestCase
{
    use TestKit;

    private ServerRequestInterface&MockObject $mockedRequest;
    private RouteInterface&MockObject $mockedRoute;

    protected function setUp(): void
    {
        $this->mockedRequest = $this->createMock(ServerRequestInterface::class);
        $this->mockedRoute = $this->createMock(RouteInterface::class);
    }

    public function testCreate(): void
    {
        $serverRequest = ServerRequest::create($this->mockedRequest);
        $this->assertSame($serverRequest, ServerRequest::create($serverRequest));
    }

    public function testHasRouteFalse(): void
    {
        $this->mockedRequest->expects(self::once())->method('getAttribute')->with(RouteInterface::class)->willReturn(null);
        $this->assertFalse(ServerRequest::create($this->mockedRequest)->hasRoute());
    }

    public function testHasRouteTrue(): void
    {
        $this->mockedRequest->expects(self::once())->method('getAttribute')->with(RouteInterface::class)->willReturn($this->mockedRoute);
        $this->assertTrue(ServerRequest::create($this->mockedRequest)->hasRoute());
    }

    public function testGetRouteInstance(): void
    {
        $this->mockedRequest->expects(self::once())->method('getAttribute')->with(RouteInterface::class)->willReturn($this->mockedRoute);
        $this->assertSame($this->mockedRoute, ServerRequest::create($this->mockedRequest)->getRoute());
    }

    public function testGetRouteException(): void
    {
        $this->mockedRequest->expects(self::once())->method('getAttribute')->with(RouteInterface::class)->willReturn(null);
        $this->expectException(LogicException::class);
        $this->expectExceptionMessageMatches('/the request does not contain information about the requested route/');
        ServerRequest::create($this->mockedRequest)->getRoute();
    }

    #[DataProvider('getClientProducedMediaTypeDataProvider')]
    public function testGetClientProducedMediaType(string $requestContentTypeHeader, ?string $expectedMediaTypeIdentifier): void
    {
        $this->mockedRequest->expects(self::once())->method('getHeaderLine')->with('Content-Type')->willReturn($requestContentTypeHeader);
        $this->assertSame($expectedMediaTypeIdentifier, ServerRequest::create($this->mockedRequest)->getClientProducedMediaType()?->getIdentifier());
    }

    public static function getClientProducedMediaTypeDataProvider(): Generator
    {
        yield ['', null];
        yield ['application/json', 'application/json'];
        yield ['application/json; charset=UTF-8', 'application/json'];
        yield ['application/json; charset=UTF-8; profile="https://example.com/schema"', 'application/json'];
        yield ["\0application\0/\0json\0", 'application/json'];
    }

    #[DataProvider('getClientConsumedMediaTypesDataProvider')]
    public function testGetClientConsumedMediaTypes(string $requestAcceptHeader, array $expectedMediaTypeIdentifiers): void
    {
        $this->mockedRequest->expects(self::once())->method('getHeaderLine')->with('Accept')->willReturn($requestAcceptHeader);
        $mediaTypeStringifier = static fn(MediaTypeInterface $mediaType): string => $mediaType->getIdentifier();
        $this->assertSame($expectedMediaTypeIdentifiers, array_map($mediaTypeStringifier, [...ServerRequest::create($this->mockedRequest)->getClientConsumedMediaTypes()]));
    }

    public static function getClientConsumedMediaTypesDataProvider(): Generator
    {
        yield ['', []];
        yield ['application/json', ['application/json']];
        yield ['application/json, application/xml', ['application/json', 'application/xml']];
        yield ['application/json; q=0.5, application/xml', ['application/xml', 'application/json']];
        yield ['application/json; q=0.5, application/xml; q=0.75', ['application/xml', 'application/json']];
        yield ['application/json; q=0.5, application/xml; q=0.25', ['application/json', 'application/xml']];
        yield ['application/json; q="0.5", application/xml; q=0.75', ['application/xml', 'application/json']];
        yield ['application/json; q=0.5, application/xml; q="0.25"', ['application/json', 'application/xml']];
    }

    #[DataProvider('getClientPreferredMediaTypeDataProvider')]
    public function testGetClientPreferredMediaType(string $requestAcceptHeader, array $serverProducedMediaTypeIdentifiers, ?string $expectedMediaTypeIdentifier): void
    {
        $this->mockedRequest->expects(self::exactly(empty($serverProducedMediaTypeIdentifiers) ? 0 : 1))->method('getHeaderLine')->with('Accept')->willReturn($requestAcceptHeader);
        $this->assertSame($expectedMediaTypeIdentifier, ServerRequest::create($this->mockedRequest)->getClientPreferredMediaType(...array_map($this->mockMediaType(...), $serverProducedMediaTypeIdentifiers))?->getIdentifier());
    }

    public static function getClientPreferredMediaTypeDataProvider(): Generator
    {
        yield ['', [], null];
        yield ['application/json', [], null];
        yield ['', ['application/json'], null];
        yield ['application/json', ['application/xml'], null];
        yield ['application/json', ['application/json'], 'application/json'];
        yield ['application/json, application/xml', ['application/json'], 'application/json'];
        yield ['application/json, application/xml', ['application/xml'], 'application/xml'];
        yield ['application/json, application/xml', ['application/json', 'application/xml'], 'application/json'];
        yield ['application/json; q=0.5, application/xml', ['application/json', 'application/xml'], 'application/xml'];
        yield ['application/json; q=0.5, application/xml; q=0.75', ['application/json', 'application/xml'], 'application/xml'];
        yield ['application/json; q=0.5, application/xml; q=0.25', ['application/json', 'application/xml'], 'application/json'];
        yield ['application/json; q="0.5", application/xml; q=0.75', ['application/json', 'application/xml'], 'application/xml'];
        yield ['application/json; q=0.5, application/xml; q="0.25"', ['application/json', 'application/xml'], 'application/json'];
        yield ['APPLICATION/JSON', ['application/json'], 'application/json'];
        yield ['application/json', ['APPLICATION/JSON'], 'APPLICATION/JSON'];
    }

    #[DataProvider('serverConsumesMediaTypeDataProvider')]
    public function testServerConsumesMediaType(string $clientProducedMediaTypeIdentifier, array $serverConsumedMediaTypeIdentifiers, bool $expectedResult): void
    {
        $this->mockedRequest->expects(self::once())->method('getAttribute')->with(RouteInterface::class)->willReturn($this->mockedRoute);
        $this->mockedRoute->expects(self::once())->method('getConsumedMediaTypes')->willReturn(array_map($this->mockMediaType(...), $serverConsumedMediaTypeIdentifiers));
        $this->assertSame($expectedResult, ServerRequest::create($this->mockedRequest)->serverConsumesMediaType($this->mockMediaType($clientProducedMediaTypeIdentifier)));
    }

    public static function serverConsumesMediaTypeDataProvider(): Generator
    {
        yield ['application/json', [], false];
        yield ['application/json', ['application/xml'], false];
        yield ['application/json', ['application/json'], true];
        yield ['application/json', ['application/xml', 'application/json'], true];
        yield ['application/json', ['APPLICATION/JSON'], true];
        yield ['APPLICATION/JSON', ['application/json'], true];
    }

    #[DataProvider('getClientConsumedLocalesDataProvider')]
    public function testGetClientConsumedLocales(string $requestAcceptLanguageHeader, array $expectedLocaleCodes): void
    {
        $this->mockedRequest->expects(self::once())->method('getHeaderLine')->with('Accept-Language')->willReturn($requestAcceptLanguageHeader);
        $localeStringifier = static fn(LocaleInterface $locale): string => $locale->getLanguageCode() . '-' . ($locale->getRegionCode() ?? '?');
        $this->assertSame($expectedLocaleCodes, array_map($localeStringifier, [...ServerRequest::create($this->mockedRequest)->getClientConsumedLocales()]));
    }

    public static function getClientConsumedLocalesDataProvider(): Generator
    {
        yield ['', []];
        yield ['sr', ['sr-?']];
        yield ['sr-RS', ['sr-RS']];
        yield ['sr-RS, bs-BA', ['sr-RS', 'bs-BA']];
        yield ['sr-RS; q=0.5, bs-BA', ['bs-BA', 'sr-RS']];
        yield ['sr-RS; q=0.5, bs-BA; q=0.75', ['bs-BA', 'sr-RS']];
        yield ['sr-RS; q=0.5, bs-BA; q=0.25', ['sr-RS', 'bs-BA']];
        yield ['sr-RS; q="0.5", bs-BA; q=0.75', ['bs-BA', 'sr-RS']];
        yield ['sr-RS; q=0.5, bs-BA; q="0.25"', ['sr-RS', 'bs-BA']];
        yield ['-', []];
    }

    #[DataProvider('getClientPreferredLanguageDataProvider')]
    public function testGetClientPreferredLanguage(string $requestAcceptLanguageHeader, array $serverProducedLanguageCodes, ?string $expectedLanguageCode): void
    {
        $this->mockedRequest->expects(self::exactly(empty($serverProducedLanguageCodes) ? 0 : 1))->method('getHeaderLine')->with('Accept-Language')->willReturn($requestAcceptLanguageHeader);
        $this->assertSame($expectedLanguageCode, ServerRequest::create($this->mockedRequest)->getClientPreferredLanguage(...array_map($this->mockLanguage(...), $serverProducedLanguageCodes))?->getCode());
    }

    public static function getClientPreferredLanguageDataProvider(): Generator
    {
        yield ['', [], null];
        yield ['sr', [], null];
        yield ['', ['sr'], null];
        yield ['sr', ['bs'], null];
        yield ['sr', ['sr'], 'sr'];
        yield ['sr, bs', ['sr'], 'sr'];
        yield ['sr, bs', ['bs'], 'bs'];
        yield ['sr, bs', ['sr', 'bs'], 'sr'];
        yield ['sr; q=0.5, bs', ['sr', 'bs'], 'bs'];
        yield ['sr; q=0.5, bs; q=0.75', ['sr', 'bs'], 'bs'];
        yield ['sr; q=0.5, bs; q=0.25', ['sr', 'bs'], 'sr'];
        yield ['sr; q="0.5", bs; q=0.75', ['sr', 'bs'], 'bs'];
        yield ['sr; q=0.5, bs; q="0.25"', ['sr', 'bs'], 'sr'];
        yield ['sr-RS, bs-BA', ['sr'], 'sr'];
        yield ['sr-RS, bs-BA', ['bs'], 'bs'];
    }

    public function testGetProtocolVersion(): void
    {
        $this->mockedRequest->expects(self::once())->method('getProtocolVersion')->willReturn('1.1');
        $this->assertSame('1.1', ServerRequest::create($this->mockedRequest)->getProtocolVersion());
    }

    public function testGetHeaders(): void
    {
        $this->mockedRequest->expects(self::once())->method('getHeaders')->willReturn(['foo' => ['bar']]);
        $this->assertSame(['foo' => ['bar']], ServerRequest::create($this->mockedRequest)->getHeaders());
    }

    public function testHasHeaderTrue(): void
    {
        $this->mockedRequest->expects(self::once())->method('hasHeader')->with('x-foo')->willReturn(true);
        $this->assertTrue(ServerRequest::create($this->mockedRequest)->hasHeader('x-foo'));
    }

    public function testHasHeaderFalse(): void
    {
        $this->mockedRequest->expects(self::once())->method('hasHeader')->with('x-foo')->willReturn(false);
        $this->assertFalse(ServerRequest::create($this->mockedRequest)->hasHeader('x-foo'));
    }

    public function testGetHeader(): void
    {
        $this->mockedRequest->expects(self::once())->method('getHeader')->with('x-foo')->willReturn(['bar']);
        $this->assertSame(['bar'], ServerRequest::create($this->mockedRequest)->getHeader('x-foo'));
    }

    public function testGetHeaderLine(): void
    {
        $this->mockedRequest->expects(self::once())->method('getHeaderLine')->with('x-foo')->willReturn('bar');
        $this->assertSame('bar', ServerRequest::create($this->mockedRequest)->getHeaderLine('x-foo'));
    }

    public function testGetBody(): void
    {
        $body = $this->createMock(StreamInterface::class);
        $this->mockedRequest->expects(self::once())->method('getBody')->willReturn($body);
        $this->assertSame($body, ServerRequest::create($this->mockedRequest)->getBody());
    }

    public function testGetMethod(): void
    {
        $this->mockedRequest->expects(self::once())->method('getMethod')->willReturn('GET');
        $this->assertSame('GET', ServerRequest::create($this->mockedRequest)->getMethod());
    }

    public function testGetUri(): void
    {
        $uri = $this->createMock(UriInterface::class);
        $this->mockedRequest->expects(self::once())->method('getUri')->willReturn($uri);
        $this->assertSame($uri, ServerRequest::create($this->mockedRequest)->getUri());
    }

    public function testGetRequestTarget(): void
    {
        $this->mockedRequest->expects(self::once())->method('getRequestTarget')->willReturn('/');
        $this->assertSame('/', ServerRequest::create($this->mockedRequest)->getRequestTarget());
    }

    public function testGetServerParams(): void
    {
        $this->mockedRequest->expects(self::once())->method('getServerParams')->willReturn(['USER' => 'foo']);
        $this->assertSame(['USER' => 'foo'], ServerRequest::create($this->mockedRequest)->getServerParams());
    }

    public function testGetQueryParams(): void
    {
        $this->mockedRequest->expects(self::once())->method('getQueryParams')->willReturn(['foo' => 'bar']);
        $this->assertSame(['foo' => 'bar'], ServerRequest::create($this->mockedRequest)->getQueryParams());
    }

    public function testGetCookieParams(): void
    {
        $this->mockedRequest->expects(self::once())->method('getCookieParams')->willReturn(['foo' => 'bar']);
        $this->assertSame(['foo' => 'bar'], ServerRequest::create($this->mockedRequest)->getCookieParams());
    }

    public function testGetUploadedFiles(): void
    {
        $uploadedFile = $this->createMock(UploadedFileInterface::class);
        $this->mockedRequest->expects(self::once())->method('getUploadedFiles')->willReturn(['foo' => $uploadedFile]);
        $this->assertSame(['foo' => $uploadedFile], ServerRequest::create($this->mockedRequest)->getUploadedFiles());
    }

    public function testGetParsedBody(): void
    {
        $this->mockedRequest->expects(self::once())->method('getParsedBody')->willReturn(['foo' => 'bar']);
        $this->assertSame(['foo' => 'bar'], ServerRequest::create($this->mockedRequest)->getParsedBody());
    }

    public function testGetAttributes(): void
    {
        $this->mockedRequest->expects(self::once())->method('getAttributes')->willReturn(['foo' => 'bar']);
        $this->assertSame(['foo' => 'bar'], ServerRequest::create($this->mockedRequest)->getAttributes());
    }

    public function testGetAttribute(): void
    {
        $this->mockedRequest->expects(self::once())->method('getAttribute')->with('foo', 'bar')->willReturn('bar');
        $this->assertSame('bar', ServerRequest::create($this->mockedRequest)->getAttribute('foo', 'bar'));
    }

    public function testWithProtocolVersion(): void
    {
        $this->mockedRequest->expects(self::once())->method('withProtocolVersion')->with('1.1')->willReturn($this->mockedRequest);
        $serverRequest = ServerRequest::create($this->mockedRequest);
        $this->assertNotSame($serverRequest, $serverRequest->withProtocolVersion('1.1'));
    }

    public function testWithHeader(): void
    {
        $this->mockedRequest->expects(self::once())->method('withHeader')->with('x-foo', 'bar')->willReturn($this->mockedRequest);
        $serverRequest = ServerRequest::create($this->mockedRequest);
        $this->assertNotSame($serverRequest, $serverRequest->withHeader('x-foo', 'bar'));
    }

    public function testWithAddedHeader(): void
    {
        $this->mockedRequest->expects(self::once())->method('withAddedHeader')->with('x-foo', 'bar')->willReturn($this->mockedRequest);
        $serverRequest = ServerRequest::create($this->mockedRequest);
        $this->assertNotSame($serverRequest, $serverRequest->withAddedHeader('x-foo', 'bar'));
    }

    public function testWithoutHeader(): void
    {
        $this->mockedRequest->expects(self::once())->method('withoutHeader')->with('x-foo')->willReturn($this->mockedRequest);
        $serverRequest = ServerRequest::create($this->mockedRequest);
        $this->assertNotSame($serverRequest, $serverRequest->withoutHeader('x-foo'));
    }

    public function testWithBody(): void
    {
        $body = $this->createMock(StreamInterface::class);
        $this->mockedRequest->expects(self::once())->method('withBody')->with($body)->willReturn($this->mockedRequest);
        $serverRequest = ServerRequest::create($this->mockedRequest);
        $this->assertNotSame($serverRequest, $serverRequest->withBody($body));
    }

    public function testWithMethod(): void
    {
        $this->mockedRequest->expects(self::once())->method('withMethod')->with('GET')->willReturn($this->mockedRequest);
        $serverRequest = ServerRequest::create($this->mockedRequest);
        $this->assertNotSame($serverRequest, $serverRequest->withMethod('GET'));
    }

    public function testWithUri(): void
    {
        $uri = $this->createMock(UriInterface::class);
        $this->mockedRequest->expects(self::once())->method('withUri')->with($uri)->willReturn($this->mockedRequest);
        $serverRequest = ServerRequest::create($this->mockedRequest);
        $this->assertNotSame($serverRequest, $serverRequest->withUri($uri));
    }

    public function testWithRequestTarget(): void
    {
        $this->mockedRequest->expects(self::once())->method('withRequestTarget')->with('/')->willReturn($this->mockedRequest);
        $serverRequest = ServerRequest::create($this->mockedRequest);
        $this->assertNotSame($serverRequest, $serverRequest->withRequestTarget('/'));
    }

    public function testWithQueryParams(): void
    {
        $this->mockedRequest->expects(self::once())->method('withQueryParams')->with(['foo' => 'bar'])->willReturn($this->mockedRequest);
        $serverRequest = ServerRequest::create($this->mockedRequest);
        $this->assertNotSame($serverRequest, $serverRequest->withQueryParams(['foo' => 'bar']));
    }

    public function testWithCookieParams(): void
    {
        $this->mockedRequest->expects(self::once())->method('withCookieParams')->with(['foo' => 'bar'])->willReturn($this->mockedRequest);
        $serverRequest = ServerRequest::create($this->mockedRequest);
        $this->assertNotSame($serverRequest, $serverRequest->withCookieParams(['foo' => 'bar']));
    }

    public function testWithUploadedFiles(): void
    {
        $uploadFile = $this->createMock(UploadedFileInterface::class);
        $this->mockedRequest->expects(self::once())->method('withUploadedFiles')->with(['foo' => $uploadFile])->willReturn($this->mockedRequest);
        $serverRequest = ServerRequest::create($this->mockedRequest);
        $this->assertNotSame($serverRequest, $serverRequest->withUploadedFiles(['foo' => $uploadFile]));
    }

    public function testWithParsedBody(): void
    {
        $this->mockedRequest->expects(self::once())->method('withParsedBody')->with(['foo' => 'bar'])->willReturn($this->mockedRequest);
        $serverRequest = ServerRequest::create($this->mockedRequest);
        $this->assertNotSame($serverRequest, $serverRequest->withParsedBody(['foo' => 'bar']));
    }

    public function testWithAttribute(): void
    {
        $this->mockedRequest->expects(self::once())->method('withAttribute')->with('foo', 'bar')->willReturn($this->mockedRequest);
        $serverRequest = ServerRequest::create($this->mockedRequest);
        $this->assertNotSame($serverRequest, $serverRequest->withAttribute('foo', 'bar'));
    }

    public function testWithoutAttribute(): void
    {
        $this->mockedRequest->expects(self::once())->method('withoutAttribute')->with('foo')->willReturn($this->mockedRequest);
        $serverRequest = ServerRequest::create($this->mockedRequest);
        $this->assertNotSame($serverRequest, $serverRequest->withoutAttribute('foo'));
    }
}
