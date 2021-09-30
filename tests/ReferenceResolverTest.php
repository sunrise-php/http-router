<?php declare(strict_types=1);

namespace Sunrise\Http\Router\Test;

/**
 * Import classes
 */
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ServerRequestInterface;
use Sunrise\Http\Router\Exception\UnresolvableReferenceException;
use Sunrise\Http\Router\ReferenceResolver;
use Sunrise\Http\Router\ReferenceResolverInterface;
use ReflectionClass;
use ReflectionMethod;

/**
 * ReferenceResolverTest
 */
class ReferenceResolverTest extends TestCase
{
    use Fixture\ContainerAwareTrait;

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
        $container = $this->getContainer();
        $resolver = new ReferenceResolver();
        $resolver->setContainer($container);

        $container->storage[Fixture\Controllers\BlankController::class] = new Fixture\Controllers\BlankController();
        $requestHandler = $resolver->toRequestHandler(Fixture\Controllers\BlankController::class);
        $this->assertSame($container->storage[Fixture\Controllers\BlankController::class], $requestHandler);

        $container->storage[Fixture\Middlewares\BlankMiddleware::class] = new Fixture\Middlewares\BlankMiddleware();
        $middleware = $resolver->toMiddleware(Fixture\Middlewares\BlankMiddleware::class);
        $this->assertSame($container->storage[Fixture\Middlewares\BlankMiddleware::class], $middleware);
    }

    /**
     * @return void
     */
    public function testRequestHandler() : void
    {
        $request = $this->createMock(ServerRequestInterface::class);
        $resolver = new ReferenceResolver();

        $requestHandler = new Fixture\Controllers\BlankController();
        $this->assertSame($requestHandler, $resolver->toRequestHandler($requestHandler));

        $requestHandler = $resolver->toRequestHandler(function ($request) {
            return (new Fixture\Controllers\BlankController)->handle($request);
        });

        $response = $requestHandler->handle($request);
        $this->assertSame(200, $response->getStatusCode());

        $requestHandler = $resolver->toRequestHandler(new ReflectionClass(Fixture\Controllers\BlankController::class));
        $response = $requestHandler->handle($request);
        $this->assertSame(200, $response->getStatusCode());

        $requestHandler = $resolver->toRequestHandler(new ReflectionMethod(
            Fixture\Controllers\BlankController::class,
            '__invoke'
        ));

        $response = $requestHandler->handle($request);
        $this->assertSame(200, $response->getStatusCode());

        $requestHandler = $resolver->toRequestHandler([Fixture\Controllers\BlankController::class, '__invoke']);
        $response = $requestHandler->handle($request);
        $this->assertSame(200, $response->getStatusCode());

        $requestHandler = $resolver->toRequestHandler(Fixture\Controllers\BlankController::class);
        $response = $requestHandler->handle($request);
        $this->assertSame(200, $response->getStatusCode());
    }

    /**
     * @return void
     */
    public function testMiddleware() : void
    {
        $request = $this->createMock(ServerRequestInterface::class);
        $resolver = new ReferenceResolver();
        $requestHandler = new Fixture\Controllers\BlankController();

        $middleware = new Fixture\Middlewares\BlankMiddleware();
        $this->assertSame($middleware, $resolver->toMiddleware($middleware));

        $middleware = $resolver->toMiddleware(function ($request, $handler) {
            return (new Fixture\Middlewares\BlankMiddleware)->process($request, $handler);
        });

        $response = $middleware->process($request, $requestHandler);
        $this->assertSame(200, $response->getStatusCode());

        $middleware = $resolver->toMiddleware(new ReflectionClass(Fixture\Middlewares\BlankMiddleware::class));
        $response = $middleware->process($request, $requestHandler);
        $this->assertSame(200, $response->getStatusCode());

        $middleware = $resolver->toMiddleware(new ReflectionMethod(
            Fixture\Middlewares\BlankMiddleware::class,
            '__invoke'
        ));

        $response = $middleware->process($request, $requestHandler);
        $this->assertSame(200, $response->getStatusCode());

        $middleware = $resolver->toMiddleware([Fixture\Middlewares\BlankMiddleware::class, '__invoke']);
        $response = $middleware->process($request, $requestHandler);
        $this->assertSame(200, $response->getStatusCode());

        $middleware = $resolver->toMiddleware(Fixture\Middlewares\BlankMiddleware::class);
        $response = $middleware->process($request, $requestHandler);
        $this->assertSame(200, $response->getStatusCode());
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
            [new ReflectionClass(Fixture\Middlewares\BlankMiddleware::class)],
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
            [new ReflectionClass(Fixture\Controllers\BlankController::class)],
            [['unknownClass', 'unknownMethod']],
            ['unknownClass'],
            [null],
        ];
    }
}
