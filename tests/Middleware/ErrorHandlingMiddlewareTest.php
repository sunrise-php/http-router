<?php

declare(strict_types=1);

namespace Sunrise\Http\Router\Tests\Middleware;

use Exception;
use Generator;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\StreamFactoryInterface;
use Psr\Http\Message\StreamInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Psr\Log\LoggerInterface;
use Sunrise\Coder\CodecManagerInterface;
use Sunrise\Coder\MediaTypeInterface;
use Sunrise\Http\Router\Dictionary\ErrorMessage;
use Sunrise\Http\Router\Dictionary\TranslationDomain;
use Sunrise\Http\Router\Exception\HttpExceptionFactory;
use Sunrise\Http\Router\LanguageInterface;
use Sunrise\Http\Router\Middleware\ErrorHandlingMiddleware;
use Sunrise\Http\Router\Tests\TestKit;
use Sunrise\Translator\TranslatorManagerInterface;

use function serialize;
use function strtr;

final class ErrorHandlingMiddlewareTest extends TestCase
{
    use TestKit;

    private ServerRequestInterface&MockObject $mockedServerRequest;
    private RequestHandlerInterface&MockObject $mockedRequestHandler;
    private ResponseFactoryInterface&MockObject $mockedResponseFactory;
    private ResponseInterface&MockObject $mockedResponse;
    private StreamFactoryInterface&MockObject $mockedStreamFactory;
    private StreamInterface&MockObject $mockedStream;
    private CodecManagerInterface&MockObject $mockedCodecManager;
    private MediaTypeInterface&MockObject $mockedDefaultMediaType;
    private TranslatorManagerInterface&MockObject $mockedTranslatorManager;
    private LanguageInterface&MockObject $mockedDefaultLanguage;
    private LoggerInterface&MockObject $mockedLogger;

    protected function setUp(): void
    {
        $this->mockedServerRequest = $this->createMock(ServerRequestInterface::class);
        $this->mockedRequestHandler = $this->createMock(RequestHandlerInterface::class);
        $this->mockedResponseFactory = $this->createMock(ResponseFactoryInterface::class);
        $this->mockedResponse = $this->createMock(ResponseInterface::class);
        $this->mockedStreamFactory = $this->createMock(StreamFactoryInterface::class);
        $this->mockedStream = $this->createMock(StreamInterface::class);
        $this->mockedCodecManager = $this->createMock(CodecManagerInterface::class);
        $this->mockedDefaultMediaType = $this->createMock(MediaTypeInterface::class);
        $this->mockedTranslatorManager = $this->createMock(TranslatorManagerInterface::class);
        $this->mockedDefaultLanguage = $this->createMock(LanguageInterface::class);
        $this->mockedLogger = $this->createMock(LoggerInterface::class);
    }

    private function createErrorHandlingMiddleware(
        array $codecContext = [],
        array $producedMediaTypes = [],
        ?MediaTypeInterface $defaultMediaType = null,
        array $producedLanguages = [],
        ?LanguageInterface $defaultLanguage = null,
        ?int $fatalErrorStatusCode = null,
        ?string $fatalErrorMessage = null,
    ): ErrorHandlingMiddleware {
        return new ErrorHandlingMiddleware(
            responseFactory: $this->mockedResponseFactory,
            streamFactory: $this->mockedStreamFactory,
            codecManager: $this->mockedCodecManager,
            codecContext: $codecContext,
            producedMediaTypes: $producedMediaTypes,
            defaultMediaType: $defaultMediaType ?? $this->mockedDefaultMediaType,
            translatorManager: $this->mockedTranslatorManager,
            producedLanguages: $producedLanguages,
            defaultLanguage: $defaultLanguage ?? $this->mockedDefaultLanguage,
            logger: $this->mockedLogger,
            fatalErrorStatusCode: $fatalErrorStatusCode,
            fatalErrorMessage: $fatalErrorMessage,
        );
    }

    private function runErrorHandlingMiddleware(
        array $codecContext = [],
        array $producedMediaTypes = [],
        ?MediaTypeInterface $defaultMediaType = null,
        array $producedLanguages = [],
        ?LanguageInterface $defaultLanguage = null,
        ?int $fatalErrorStatusCode = null,
        ?string $fatalErrorMessage = null,
    ): ResponseInterface {
        return $this->createErrorHandlingMiddleware(
            codecContext: $codecContext,
            producedMediaTypes: $producedMediaTypes,
            defaultMediaType: $defaultMediaType,
            producedLanguages: $producedLanguages,
            defaultLanguage: $defaultLanguage,
            fatalErrorStatusCode: $fatalErrorStatusCode,
            fatalErrorMessage: $fatalErrorMessage,
        )->process(
            request: $this->mockedServerRequest,
            handler: $this->mockedRequestHandler,
        );
    }

    public function testNoError(): void
    {
        $this->mockedRequestHandler->expects(self::once())->method('handle')->with($this->mockedServerRequest)->willReturn($this->mockedResponse);
        self::assertSame($this->mockedResponse, $this->runErrorHandlingMiddleware());
    }

    public function testHttpError(): void
    {
        $httpError = HttpExceptionFactory::invalidBody('[{0}{1}]');
        $httpError->addMessagePlaceholder('{0}', '0');
        $httpError->addMessagePlaceholder('{1}', '1');
        $httpError->addHeaderField('X-0', '0');
        $httpError->addHeaderField('X-1', '1');
        $httpError->addConstraintViolation($this->mockConstraintViolation('[23] ', '[{2}{3}]', ['{2}' => '2', '{3}' => '3'], translationDomain: 'bar'));
        $httpError->addConstraintViolation($this->mockConstraintViolation('[45] ', '[{4}{5}]', ['{4}' => '4', '{5}' => '5'], translationDomain: 'baz'));
        $httpError->setTranslationDomain('foo');

        $expectedResponse = $this->createMock(ResponseInterface::class);

        $this->mockedRequestHandler->expects(self::once())->method('handle')->with($this->mockedServerRequest)->willThrowException($httpError);
        $this->mockedServerRequest->expects(self::never())->method('getHeaderLine');
        $this->mockedResponseFactory->expects(self::once())->method('createResponse')->with($httpError->getCode())->willReturn($expectedResponse);
        $this->mockedDefaultMediaType->expects(self::once())->method('getIdentifier')->willReturn('application/json');

        $expectedResponse->expects(self::exactly(3))->method('withHeader')->withAnyParameters()->willReturnCallback(
            static function ($name, $value) use ($expectedResponse) {
                self::assertContains(
                    [$name, $value],
                    [
                        ['X-0', '0'],
                        ['X-1', '1'],
                        ['Content-Type', 'application/json; charset=UTF-8'],
                    ],
                );

                return $expectedResponse;
            }
        );

        $this->mockedDefaultLanguage->expects(self::exactly(3))->method('getCode')->willReturn('sr');

        $this->mockedTranslatorManager->expects(self::exactly(3))->method('translate')->withAnyParameters()->willReturnCallback(
            static function ($domain, $locale, $template, array $placeholders) {
                self::assertContains(
                    [$domain, $locale, $template, $placeholders],
                    [
                        ['foo', 'sr', '[{0}{1}]', ['{0}' => '0', '{1}' => '1']],
                        ['bar', 'sr', '[{2}{3}]', ['{2}' => '2', '{3}' => '3']],
                        ['baz', 'sr', '[{4}{5}]', ['{4}' => '4', '{5}' => '5']],
                    ],
                );

                return strtr($template, $placeholders);
            }
        );

        $this->mockedCodecManager->expects(self::once())->method('encode')->withAnyParameters()->willReturnCallback(
            function ($mediaType, $data, $context) {
                self::assertSame($this->mockedDefaultMediaType, $mediaType);
                self::assertSame(['foo' => 'bar'], $context);

                return serialize($data);
            }
        );

        $this->mockedStreamFactory->expects(self::once())->method('createStream')->withAnyParameters()->willReturnCallback(
            function (string $content) {
                self::assertStringContainsString('[01]', $content);
                self::assertStringContainsString('[23]', $content);
                self::assertStringContainsString('[45]', $content);

                return $this->mockedStream;
            }
        );

        $expectedResponse->expects(self::once())->method('withBody')->with($this->mockedStream)->willReturnSelf();

        self::assertSame($expectedResponse, $this->runErrorHandlingMiddleware(codecContext: ['foo' => 'bar']));
    }

    #[DataProvider('fatalErrorDataProvider')]
    public function testFatalError(
        ?int $fatalErrorStatusCode,
        int $expectedStatusCode,
        ?string $fatalErrorMessage,
        string $expectedMessage,
        array $codecContext,
    ): void {
        $fatalError = new Exception('alarm');
        $expectedResponse = $this->createMock(ResponseInterface::class);

        $this->mockedLogger->expects(self::once())->method('error')->with($fatalError->getMessage(), ['error' => $fatalError, 'request' => $this->mockedServerRequest]);
        $this->mockedRequestHandler->expects(self::once())->method('handle')->with($this->mockedServerRequest)->willThrowException($fatalError);
        $this->mockedServerRequest->expects(self::never())->method('getHeaderLine');
        $this->mockedResponseFactory->expects(self::once())->method('createResponse')->with($expectedStatusCode)->willReturn($expectedResponse);
        $this->mockedDefaultMediaType->expects(self::once())->method('getIdentifier')->willReturn('application/json');
        $expectedResponse->expects(self::once())->method('withHeader')->with('Content-Type', 'application/json; charset=UTF-8')->willReturn($expectedResponse);
        $this->mockedDefaultLanguage->expects(self::once())->method('getCode')->willReturn('sr');
        $this->mockedTranslatorManager->expects(self::once())->method('translate')->with(TranslationDomain::ROUTER, 'sr', $expectedMessage, [])->willReturnArgument(2);

        $this->mockedCodecManager->expects(self::once())->method('encode')->withAnyParameters()->willReturnCallback(
            function ($mediaType, $data, $context) use ($codecContext) {
                self::assertSame($this->mockedDefaultMediaType, $mediaType);
                self::assertSame($codecContext, $context);

                return serialize($data);
            }
        );

        $this->mockedStreamFactory->expects(self::once())->method('createStream')->withAnyParameters()->willReturnCallback(
            function (string $content) use ($expectedMessage) {
                self::assertStringContainsString($expectedMessage, $content);

                return $this->mockedStream;
            }
        );

        $expectedResponse->expects(self::once())->method('withBody')->with($this->mockedStream)->willReturnSelf();

        self::assertSame($expectedResponse, $this->runErrorHandlingMiddleware(
            codecContext: $codecContext,
            fatalErrorStatusCode: $fatalErrorStatusCode,
            fatalErrorMessage: $fatalErrorMessage,
        ));
    }

    public static function fatalErrorDataProvider(): Generator
    {
        yield [null, 500, null, ErrorMessage::INTERNAL_SERVER_ERROR, []];
        yield [null, 500, null, ErrorMessage::INTERNAL_SERVER_ERROR, ['foo' => 'bar']];

        yield [503, 503, 'no panic!', 'no panic!', []];
        yield [503, 503, 'no panic!', 'no panic!', ['foo' => 'bar']];
    }
}
