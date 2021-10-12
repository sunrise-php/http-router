<?php declare(strict_types=1);

namespace Sunrise\Http\Router\Tests;

/**
 * Import classes
 */
use PHPUnit\Framework\TestCase;
use Sunrise\Http\Router\Exception\UnresolvableReferenceException;
use Sunrise\Http\Router\ReferenceResolver;
use Sunrise\Http\Router\ReferenceResolverInterface;

/**
 * ReferenceResolverTest
 */
class ReferenceResolverTest extends TestCase
{
    use Fixtures\ContainerAwareTrait;

    /**
     * @return void
     */
    public function testContracts() : void
    {
        $resolver = new ReferenceResolver();

        $this->assertInstanceOf(ReferenceResolverInterface::class, $resolver);
    }

    /**
     * @return void
     */
    public function testContainer() : void
    {
        $container = $this->getContainer([
            Fixtures\Controllers\BlankController::class => new Fixtures\Controllers\BlankController(),
            Fixtures\Middlewares\BlankMiddleware::class => new Fixtures\Middlewares\BlankMiddleware(),
        ]);

        $resolver = new ReferenceResolver();

        $resolver->setContainer($container);
        $this->assertSame($container, $resolver->getContainer());

        $reference = Fixtures\Controllers\BlankController::class;
        $requestHandler = $resolver->toRequestHandler($reference);
        $this->assertSame($container->storage[Fixtures\Controllers\BlankController::class], $requestHandler);

        $reference = [Fixtures\Controllers\BlankController::class, '__invoke'];
        $requestHandler = $resolver->toRequestHandler($reference);
        $requestHandlerCallback = $requestHandler->getCallback();
        $this->assertSame($container->storage[Fixtures\Controllers\BlankController::class], $requestHandlerCallback[0]);

        $reference = Fixtures\Middlewares\BlankMiddleware::class;
        $middleware = $resolver->toMiddleware($reference);
        $this->assertSame($container->storage[Fixtures\Middlewares\BlankMiddleware::class], $middleware);

        $reference = [Fixtures\Middlewares\BlankMiddleware::class, '__invoke'];
        $middleware = $resolver->toMiddleware($reference);
        $middlewareCallback = $middleware->getCallback();
        $this->assertSame($container->storage[Fixtures\Middlewares\BlankMiddleware::class], $middlewareCallback[0]);
    }

    /**
     * @return void
     */
    public function testRequestHandler() : void
    {
        $resolver = new ReferenceResolver();

        $reference = new Fixtures\Controllers\BlankController();
        $requestHandler = $resolver->toRequestHandler($reference);
        $this->assertSame($reference, $requestHandler);

        $reference = function () {
        };

        $requestHandler = $resolver->toRequestHandler($reference);
        $this->assertSame($reference, $requestHandler->getCallback());

        $reference = Fixtures\Controllers\BlankController::class;
        $requestHandler = $resolver->toRequestHandler($reference);
        $this->assertInstanceOf($reference, $requestHandler);

        $reference = [Fixtures\Controllers\BlankController::class, '__invoke'];
        $requestHandler = $resolver->toRequestHandler($reference);
        $this->assertInstanceOf($reference[0], $requestHandler->getCallback()[0]);
        $this->assertSame($reference[1], $requestHandler->getCallback()[1]);
    }

    /**
     * @return void
     */
    public function testMiddleware() : void
    {
        $resolver = new ReferenceResolver();

        $reference = new Fixtures\Middlewares\BlankMiddleware();
        $requestHandler = $resolver->toMiddleware($reference);
        $this->assertSame($reference, $requestHandler);

        $reference = function () {
        };

        $requestHandler = $resolver->toMiddleware($reference);
        $this->assertSame($reference, $requestHandler->getCallback());

        $reference = Fixtures\Middlewares\BlankMiddleware::class;
        $requestHandler = $resolver->toMiddleware($reference);
        $this->assertInstanceOf($reference, $requestHandler);

        $reference = [Fixtures\Middlewares\BlankMiddleware::class, '__invoke'];
        $requestHandler = $resolver->toMiddleware($reference);
        $this->assertInstanceOf($reference[0], $requestHandler->getCallback()[0]);
        $this->assertSame($reference[1], $requestHandler->getCallback()[1]);
    }

    /**
     * @param mixed $reference
     *
     * @return void
     *
     * @dataProvider unresolvableRequestHandlerReferenceDataProvider
     */
    public function testUnresolvableRequestHandler($reference) : void
    {
        $resolver = new ReferenceResolver();
        $this->expectException(UnresolvableReferenceException::class);
        $resolver->toRequestHandler($reference);
    }

    /**
     * @param mixed $reference
     *
     * @return void
     *
     * @dataProvider unresolvableMiddlewareReferenceDataProvider
     */
    public function testUnresolvableMiddleware($reference) : void
    {
        $resolver = new ReferenceResolver();
        $this->expectException(UnresolvableReferenceException::class);
        $resolver->toMiddleware($reference);
    }

    /**
     * @return array
     */
    public function unresolvableRequestHandlerReferenceDataProvider() : array
    {
        return [
            [['unknownClass', 'unknownMethod']],
            ['unknownClass'],
            [null],
        ];
    }

    /**
     * @return array
     */
    public function unresolvableMiddlewareReferenceDataProvider() : array
    {
        return [
            [['unknownClass', 'unknownMethod']],
            ['unknownClass'],
            [null],
        ];
    }
}
