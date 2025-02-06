<?php

declare(strict_types=1);

namespace Sunrise\Http\Router\Tests\functions;

use PHPUnit\Framework\Attributes\RunInSeparateProcess;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamInterface;

use function extension_loaded;
use function ob_end_clean;
use function ob_get_clean;
use function ob_start;
use function Sunrise\Http\Router\emit;
use function xdebug_get_headers;

final class EmitFunctionTest extends TestCase
{
    #[RunInSeparateProcess]
    public function testSendResponseHeaders(): void
    {
        extension_loaded('xdebug') or $this->markTestSkipped('Xdebug is required.');

        $headers = [
            'x-foo' => ['bar'],
            'x-bar' => ['baz', 'qux'],
        ];

        $response = $this->createMock(ResponseInterface::class);
        $response->expects(self::once())->method('getHeaders')->willReturn($headers);

        ob_start();
        emit($response);
        ob_end_clean();

        $this->assertSame([
            'x-foo: bar',
            'x-bar: baz',
            'x-bar: qux',
        ], xdebug_get_headers());
    }

    #[RunInSeparateProcess]
    public function testOutputResponseBody(): void
    {
        $body = $this->createMock(StreamInterface::class);
        $body->expects(self::once())->method('__toString')->willReturn('foo');

        $response = $this->createMock(ResponseInterface::class);
        $response->expects(self::once())->method('getHeaders')->willReturn([]);
        $response->expects(self::once())->method('getBody')->willReturn($body);

        ob_start();
        emit($response);
        $output = ob_get_clean();

        $this->assertSame('foo', $output);
    }
}
