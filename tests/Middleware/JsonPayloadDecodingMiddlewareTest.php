<?php declare(strict_types=1);

namespace Sunrise\Http\Router\Tests\Middleware;

/**
 * Import classes
 */
use PHPUnit\Framework\TestCase;
use Psr\Http\Server\MiddlewareInterface;
use Sunrise\Http\Router\Exception\UndecodablePayloadException;
use Sunrise\Http\Router\Middleware\JsonPayloadDecodingMiddleware;
use Sunrise\Http\Router\Tests\Fixtures;
use Sunrise\Http\ServerRequest\ServerRequestFactory;

/**
 * JsonPayloadDecodingMiddlewareTest
 */
class JsonPayloadDecodingMiddlewareTest extends TestCase
{

    /**
     * @return void
     */
    public function testContract() : void
    {
        $this->assertInstanceOf(MiddlewareInterface::class, new JsonPayloadDecodingMiddleware());
    }

    /**
     * @param string $mediaType
     *
     * @return void
     *
     * @dataProvider supportedMediaTypeProvider
     */
    public function testProcessWithSupportedMediaType(string $mediaType) : void
    {
        $request = (new ServerRequestFactory)->createServerRequest('GET', '/')
            ->withHeader('Content-Type', $mediaType);

        $request->getBody()->write('{"foo":"bar"}');

        $handler = new Fixtures\Controllers\BlankController();

        (new JsonPayloadDecodingMiddleware)->process($request, $handler);

        $this->assertSame(['foo' => 'bar'], $handler->getRequest()->getParsedBody());
    }

    /**
     * @param string $mediaType
     *
     * @return void
     *
     * @dataProvider unsupportedMediaTypeProvider
     */
    public function testProcessWithUnsupportedMediaType(string $mediaType) : void
    {
        $request = (new ServerRequestFactory)->createServerRequest('GET', '/')
            ->withHeader('Content-Type', $mediaType);

        $request->getBody()->write('{"foo":"bar"}');

        $handler = new Fixtures\Controllers\BlankController();

        (new JsonPayloadDecodingMiddleware)->process($request, $handler);

        $this->assertNull($handler->getRequest()->getParsedBody());
    }

    /**
     * @return void
     */
    public function testProcessWithoutMediaType() : void
    {
        $request = (new ServerRequestFactory)->createServerRequest('GET', '/');
        $request->getBody()->write('{"foo":"bar"}');

        $handler = new Fixtures\Controllers\BlankController();

        (new JsonPayloadDecodingMiddleware)->process($request, $handler);

        $this->assertNull($handler->getRequest()->getParsedBody());
    }

    /**
     * @return void
     */
    public function testProcessWithInvalidPayload() : void
    {
        $request = (new ServerRequestFactory)->createServerRequest('GET', '/')
            ->withHeader('Content-Type', 'application/json');

        $request->getBody()->write('!');

        $handler = new Fixtures\Controllers\BlankController();

        $this->expectException(UndecodablePayloadException::class);
        $this->expectExceptionMessage('Invalid Payload: Syntax error');

        (new JsonPayloadDecodingMiddleware)->process($request, $handler);
    }

    /**
     * @return list<array{0: string}>
     */
    public function supportedMediaTypeProvider() : array
    {
        return [
            ['application/json'],
            ['application/json; foo=bar'],
            ['application/json ; foo=bar'],
        ];
    }

    /**
     * @return list<array{0: string}>
     */
    public function unsupportedMediaTypeProvider() : array
    {
        return [
            ['application/jsonx'],
            ['application/json+x'],
            ['application/jsonx; foo=bar'],
            ['application/json+x; foo=bar'],
            ['application/jsonx ; foo=bar'],
            ['application/json+x ; foo=bar'],
        ];
    }
}
