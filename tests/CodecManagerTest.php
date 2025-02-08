<?php

declare(strict_types=1);

namespace Sunrise\Http\Router\Tests;

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Sunrise\Http\Router\CodecInterface;
use Sunrise\Http\Router\CodecManager;
use Sunrise\Http\Router\Exception\CodecException;

final class CodecManagerTest extends TestCase
{
    use TestKit;

    /**
     * @var list<CodecInterface&MockObject>
     */
    private array $mockedCodecs;

    /**
     * @var array<array-key, mixed>
     */
    private array $expectedCodecContext = [];

    protected function setUp(): void
    {
        $this->mockedCodecs = [
            $this->createMock(CodecInterface::class),
            $this->createMock(CodecInterface::class),
            $this->createMock(CodecInterface::class),
        ];

        $this->mockedCodecs[0]->method('getSupportedMediaTypes')->willReturn([]);
        $this->mockedCodecs[0]->expects(self::never())->method('decode');
        $this->mockedCodecs[0]->expects(self::never())->method('encode');

        $codecContextArgument = self::callback(fn(mixed $argument): bool => $argument === $this->expectedCodecContext);

        $this->mockedCodecs[1]->method('getSupportedMediaTypes')->willReturn([$this->mockMediaType('test/foo')]);
        $this->mockedCodecs[1]->method('decode')->with('1', $codecContextArgument)->willReturn(1);
        $this->mockedCodecs[1]->method('encode')->with(1, $codecContextArgument)->willReturn('1');

        $this->mockedCodecs[2]->method('getSupportedMediaTypes')->willReturn([$this->mockMediaType('test/bar'), $this->mockMediaType('test/baz')]);
        $this->mockedCodecs[2]->method('decode')->with('2', $codecContextArgument)->willReturn(2);
        $this->mockedCodecs[2]->method('encode')->with(2, $codecContextArgument)->willReturn('2');
    }

    public function testSupportsMediaType(): void
    {
        $this->assertTrue((new CodecManager($this->mockedCodecs))->supportsMediaType($this->mockMediaType('test/foo')));
        $this->assertTrue((new CodecManager($this->mockedCodecs))->supportsMediaType($this->mockMediaType('test/bar')));
        $this->assertTrue((new CodecManager($this->mockedCodecs))->supportsMediaType($this->mockMediaType('test/baz')));
        $this->assertFalse((new CodecManager($this->mockedCodecs))->supportsMediaType($this->mockMediaType('test/bat')));
    }

    public function testSupportsCaseInsensitiveMediaType(): void
    {
        $this->assertTrue((new CodecManager($this->mockedCodecs))->supportsMediaType($this->mockMediaType('TEST/FOO')));
        $this->assertTrue((new CodecManager($this->mockedCodecs))->supportsMediaType($this->mockMediaType('TEST/BAR')));
        $this->assertTrue((new CodecManager($this->mockedCodecs))->supportsMediaType($this->mockMediaType('TEST/BAZ')));
        $this->assertFalse((new CodecManager($this->mockedCodecs))->supportsMediaType($this->mockMediaType('TEST/BAT')));
    }

    public function testDecode(): void
    {
        $this->assertSame(1, (new CodecManager($this->mockedCodecs))->decode($this->mockMediaType('test/foo'), '1'));
        $this->assertSame(2, (new CodecManager($this->mockedCodecs))->decode($this->mockMediaType('test/bar'), '2'));
        $this->assertSame(2, (new CodecManager($this->mockedCodecs))->decode($this->mockMediaType('test/baz'), '2'));
    }

    public function testDecodeCaseInsensitiveMediaType(): void
    {
        $this->assertSame(1, (new CodecManager($this->mockedCodecs))->decode($this->mockMediaType('TEST/FOO'), '1'));
        $this->assertSame(2, (new CodecManager($this->mockedCodecs))->decode($this->mockMediaType('TEST/BAR'), '2'));
        $this->assertSame(2, (new CodecManager($this->mockedCodecs))->decode($this->mockMediaType('TEST/BAZ'), '2'));
    }

    public function testDecodeUnsupportedMediaType(): void
    {
        $this->expectException(CodecException::class);
        $this->expectExceptionMessage('Unsupported the "test/bat" media type.');
        (new CodecManager($this->mockedCodecs))->decode($this->mockMediaType('test/bat'), '0');
    }

    public function testDecodeWithoutCodecs(): void
    {
        $this->expectException(CodecException::class);
        $this->expectExceptionMessage('Unsupported the "test/bat" media type.');
        (new CodecManager([]))->decode($this->mockMediaType('test/bat'), '0');
    }

    public function testEncode(): void
    {
        $this->assertSame('1', (new CodecManager($this->mockedCodecs))->encode($this->mockMediaType('test/foo'), 1));
        $this->assertSame('2', (new CodecManager($this->mockedCodecs))->encode($this->mockMediaType('test/bar'), 2));
        $this->assertSame('2', (new CodecManager($this->mockedCodecs))->encode($this->mockMediaType('test/baz'), 2));
    }

    public function testEncodeCaseInsensitiveMediaType(): void
    {
        $this->assertSame('1', (new CodecManager($this->mockedCodecs))->encode($this->mockMediaType('TEST/FOO'), 1));
        $this->assertSame('2', (new CodecManager($this->mockedCodecs))->encode($this->mockMediaType('TEST/BAR'), 2));
        $this->assertSame('2', (new CodecManager($this->mockedCodecs))->encode($this->mockMediaType('TEST/BAZ'), 2));
    }

    public function testEncodeUnsupportedMediaType(): void
    {
        $this->expectException(CodecException::class);
        $this->expectExceptionMessage('Unsupported the "test/bat" media type.');
        (new CodecManager($this->mockedCodecs))->encode($this->mockMediaType('test/bat'), 0);
    }

    public function testEncodeWithoutCodecs(): void
    {
        $this->expectException(CodecException::class);
        $this->expectExceptionMessage('Unsupported the "test/bat" media type.');
        (new CodecManager([]))->encode($this->mockMediaType('test/bat'), 0);
    }

    public function testContext(): void
    {
        $codecsContext = ['foo' => 'bar', 'bar' => 'baz'];
        $codecContext = ['foo' => 'baz', 'baz' => 'qux'];

        $this->expectedCodecContext = $codecContext + $codecsContext;

        $this->assertSame(1, (new CodecManager($this->mockedCodecs, $codecsContext))->decode($this->mockMediaType('test/foo'), '1', $codecContext));
        $this->assertSame(2, (new CodecManager($this->mockedCodecs, $codecsContext))->decode($this->mockMediaType('test/bar'), '2', $codecContext));
        $this->assertSame(2, (new CodecManager($this->mockedCodecs, $codecsContext))->decode($this->mockMediaType('test/baz'), '2', $codecContext));

        $this->assertSame('1', (new CodecManager($this->mockedCodecs, $codecsContext))->encode($this->mockMediaType('test/foo'), 1, $codecContext));
        $this->assertSame('2', (new CodecManager($this->mockedCodecs, $codecsContext))->encode($this->mockMediaType('test/bar'), 2, $codecContext));
        $this->assertSame('2', (new CodecManager($this->mockedCodecs, $codecsContext))->encode($this->mockMediaType('test/baz'), 2, $codecContext));
    }
}
