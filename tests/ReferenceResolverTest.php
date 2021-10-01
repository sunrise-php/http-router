<?php declare(strict_types=1);

namespace Sunrise\Http\Router\Test;

/**
 * Import classes
 */
use PHPUnit\Framework\TestCase;
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
        $container = $this->getContainer([
            Fixture\Controllers\BlankController::class => new Fixture\Controllers\BlankController(),
            Fixture\Middlewares\BlankMiddleware::class => new Fixture\Middlewares\BlankMiddleware(),
        ]);

        $resolver = new ReferenceResolver();
        $resolver->setContainer($container);

        $this->assertSame($container, $resolver->getContainer());

        $requestHandler = $resolver->toRequestHandler(new ReflectionClass(Fixture\Controllers\BlankController::class));
        $this->assertSame($container->storage[Fixture\Controllers\BlankController::class], $requestHandler);

        $requestHandler = $resolver->toRequestHandler(new ReflectionMethod(Fixture\Controllers\BlankController::class, '__invoke'));
        $this->assertSame($container->storage[Fixture\Controllers\BlankController::class], $requestHandler->getCallback()[0]);

        $requestHandler = $resolver->toRequestHandler(Fixture\Controllers\BlankController::class);
        $this->assertSame($container->storage[Fixture\Controllers\BlankController::class], $requestHandler);

        $requestHandler = $resolver->toRequestHandler([Fixture\Controllers\BlankController::class, '__invoke']);
        $this->assertSame($container->storage[Fixture\Controllers\BlankController::class], $requestHandler->getCallback()[0]);

        $middleware = $resolver->toMiddleware(new ReflectionClass(Fixture\Middlewares\BlankMiddleware::class));
        $this->assertSame($container->storage[Fixture\Middlewares\BlankMiddleware::class], $middleware);

        $middleware = $resolver->toMiddleware(new ReflectionMethod(Fixture\Middlewares\BlankMiddleware::class, '__invoke'));
        $this->assertSame($container->storage[Fixture\Middlewares\BlankMiddleware::class], $middleware->getCallback()[0]);

        $middleware = $resolver->toMiddleware(Fixture\Middlewares\BlankMiddleware::class);
        $this->assertSame($container->storage[Fixture\Middlewares\BlankMiddleware::class], $middleware);

        $middleware = $resolver->toMiddleware([Fixture\Middlewares\BlankMiddleware::class, '__invoke']);
        $this->assertSame($container->storage[Fixture\Middlewares\BlankMiddleware::class], $middleware->getCallback()[0]);
    }

    /**
     * @return void
     */
    public function testRequestHandler() : void
    {
        $resolver = new ReferenceResolver();

        $reference = new Fixture\Controllers\BlankController();
        $requestHandler = $resolver->toRequestHandler($reference);
        $this->assertSame($reference, $requestHandler);

        $reference = function () {
        };

        $requestHandler = $resolver->toRequestHandler($reference);
        $this->assertSame($reference, $requestHandler->getCallback());

        $reference = new ReflectionClass(Fixture\Controllers\BlankController::class);
        $requestHandler = $resolver->toRequestHandler($reference);
        $this->assertInstanceOf($reference->getName(), $requestHandler);

        $reference = new ReflectionMethod(Fixture\Controllers\BlankController::class, '__invoke');
        $requestHandler = $resolver->toRequestHandler($reference);
        $this->assertInstanceOf($reference->getDeclaringClass()->getName(), $requestHandler->getCallback()[0]);
        $this->assertSame($reference->getName(), $requestHandler->getCallback()[1]);

        $reference = Fixture\Controllers\BlankController::class;
        $requestHandler = $resolver->toRequestHandler($reference);
        $this->assertInstanceOf($reference, $requestHandler);

        $reference = [Fixture\Controllers\BlankController::class, '__invoke'];
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

        $reference = new Fixture\Middlewares\BlankMiddleware();
        $requestHandler = $resolver->toMiddleware($reference);
        $this->assertSame($reference, $requestHandler);

        $reference = function () {
        };

        $requestHandler = $resolver->toMiddleware($reference);
        $this->assertSame($reference, $requestHandler->getCallback());

        $reference = new ReflectionClass(Fixture\Middlewares\BlankMiddleware::class);
        $requestHandler = $resolver->toMiddleware($reference);
        $this->assertInstanceOf($reference->getName(), $requestHandler);

        $reference = new ReflectionMethod(Fixture\Middlewares\BlankMiddleware::class, '__invoke');
        $requestHandler = $resolver->toMiddleware($reference);
        $this->assertInstanceOf($reference->getDeclaringClass()->getName(), $requestHandler->getCallback()[0]);
        $this->assertSame($reference->getName(), $requestHandler->getCallback()[1]);

        $reference = Fixture\Middlewares\BlankMiddleware::class;
        $requestHandler = $resolver->toMiddleware($reference);
        $this->assertInstanceOf($reference, $requestHandler);

        $reference = [Fixture\Middlewares\BlankMiddleware::class, '__invoke'];
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
