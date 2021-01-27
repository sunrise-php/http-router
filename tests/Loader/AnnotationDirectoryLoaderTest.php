<?php declare(strict_types=1);

namespace Sunrise\Http\Router\Tests\Loader;

/**
 * Import classes
 */
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;
use Psr\SimpleCache\CacheInterface;
use Sunrise\Http\Router\Annotation\Route as AnnotationRoute;
use Sunrise\Http\Router\Exception\InvalidDescriptorArgumentException;
use Sunrise\Http\Router\Exception\InvalidLoaderResourceException;
use Sunrise\Http\Router\Loader\AnnotationDirectoryLoader;
use Sunrise\Http\Router\Loader\DescriptorDirectoryLoader;
use Sunrise\Http\Router\Loader\LoaderInterface;
use Sunrise\Http\Router\Tests\Fixture;

/**
 * AnnotationDirectoryLoaderTest
 */
class AnnotationDirectoryLoaderTest extends TestCase
{

    /**
     * @return void
     */
    public function testConstructor() : void
    {
        $loader = new AnnotationDirectoryLoader();

        $this->assertInstanceOf(LoaderInterface::class, $loader);

        // BC 2.6.0
        $this->assertInstanceOf(DescriptorDirectoryLoader::class, $loader);
    }

    /**
     * @return void
     */
    public function testContainer() : void
    {
        $loader = new AnnotationDirectoryLoader();

        $this->assertNull($loader->getContainer());

        $container = $this->createMock(ContainerInterface::class);

        $loader->setContainer($container);

        $this->assertSame($container, $loader->getContainer());
    }

    /**
     * @return void
     */
    public function testCache() : void
    {
        $loader = new AnnotationDirectoryLoader();

        $this->assertNull($loader->getCache());

        $cache = $this->createMock(CacheInterface::class);

        $loader->setCache($cache);

        $this->assertSame($cache, $loader->getCache());
    }

    /**
     * @return void
     */
    public function testAttachInvalidResource() : void
    {
        $loader = new AnnotationDirectoryLoader();

        $this->expectException(InvalidLoaderResourceException::class);
        $this->expectExceptionMessage('The resource "undefined" is not found.');

        $loader->attach('undefined');
    }

    /**
     * @return void
     *
     * @runInSeparateProcess
     */
    public function testAttachArray() : void
    {
        $loader = new AnnotationDirectoryLoader();

        $loader->attachArray([
            __DIR__ . '/../Fixture/Annotation/Route/Valid',
            __DIR__ . '/../Fixture/Annotation/Route/Containerable',
        ]);

        $this->assertCount(5, $loader->load()->all());
    }

    /**
     * @return void
     *
     * @runInSeparateProcess
     */
    public function testLoad() : void
    {
        $loader = new AnnotationDirectoryLoader();
        $loader->attach(__DIR__ . '/../Fixture/Annotation/Route/Valid');
        $routes = $loader->load()->all();

        // test for the routes priority...
        $this->assertSame([
            'home',
            'ping',
            'sub-dir:foo',
            'sub-dir:bar',
        ], Fixture\Helper::routesToNames($routes));

        $this->assertContains([
            'name' => 'home',
            'path' => '/',
            'methods' => ['HEAD', 'GET'],
            'requestHandler' => 'Sunrise\Http\Router\Tests\Fixture\Annotation\Route\Valid\HomeRequestHandler',
            'middlewares' => [
                'Sunrise\Http\Router\Tests\Fixture\BlankMiddleware',
                'Sunrise\Http\Router\Tests\Fixture\BlankMiddleware',
            ],
            'attributes' => [
                'foo' => 'bar',
                'bar' => 'baz',
            ],
        ], Fixture\Helper::routesToArray($routes));

        $this->assertContains([
            'name' => 'ping',
            'path' => '/ping',
            'methods' => ['HEAD', 'GET'],
            'requestHandler' => 'Sunrise\Http\Router\Tests\Fixture\Annotation\Route\Valid\PingRequestHandler',
            'middlewares' => [
                'Sunrise\Http\Router\Tests\Fixture\BlankMiddleware',
                'Sunrise\Http\Router\Tests\Fixture\BlankMiddleware',
            ],
            'attributes' => [
                'foo' => 'bar',
                'bar' => 'baz',
            ],
        ], Fixture\Helper::routesToArray($routes));

        $this->assertContains([
            'name' => 'sub-dir:foo',
            'path' => '/sub-dir/foo',
            'methods' => ['HEAD', 'GET'],
            'requestHandler' => 'Sunrise\Http\Router\Tests\Fixture\Annotation\Route\Valid\FooRequestHandler',
            'middlewares' => [
                'Sunrise\Http\Router\Tests\Fixture\BlankMiddleware',
                'Sunrise\Http\Router\Tests\Fixture\BlankMiddleware',
            ],
            'attributes' => [
                'foo' => 'bar',
                'bar' => 'baz',
            ],
        ], Fixture\Helper::routesToArray($routes));

        $this->assertContains([
            'name' => 'sub-dir:bar',
            'path' => '/sub-dir/bar',
            'methods' => ['HEAD', 'GET'],
            'requestHandler' => 'Sunrise\Http\Router\Tests\Fixture\Annotation\Route\Valid\BarRequestHandler',
            'middlewares' => [
                'Sunrise\Http\Router\Tests\Fixture\BlankMiddleware',
                'Sunrise\Http\Router\Tests\Fixture\BlankMiddleware',
            ],
            'attributes' => [
                'foo' => 'bar',
                'bar' => 'baz',
            ],
        ], Fixture\Helper::routesToArray($routes));

        $this->assertContains([
            'summary' => 'the route summary',
            'description' => 'the route description',
            'tags' => ['foo', 'bar'],
        ], Fixture\Helper::routesToArray204($routes));

        $this->assertContains([
            'host' => 'localhost',
        ], Fixture\Helper::routesToArray206($routes));
    }

    /**
     * @return void
     *
     * @runInSeparateProcess
     */
    public function testLoadWithContainer() : void
    {
        $container = $this->createMock(ContainerInterface::class);

        $container->expects($this->exactly(2))
            ->method('has')
            ->willReturn(true);

        $container->expects($this->exactly(2))
            ->method('get')
            ->willReturn(new Fixture\BlankMiddlewarableRequestHandler());

        $loader = new AnnotationDirectoryLoader();
        $loader->attach(__DIR__ . '/../Fixture/Annotation/Route/Containerable');
        $loader->setContainer($container);
        $routes = $loader->load()->all();

        $this->assertContains([
            'name' => 'foo',
            'path' => '/foo',
            'methods' => ['GET'],
            'requestHandler' => 'Sunrise\Http\Router\Tests\Fixture\BlankMiddlewarableRequestHandler',
            'middlewares' => [
                'Sunrise\Http\Router\Tests\Fixture\BlankMiddlewarableRequestHandler',
            ],
            'attributes' => [
            ],
        ], Fixture\Helper::routesToArray($routes));
    }

    /**
     * @return void
     *
     * @runInSeparateProcess
     */
    public function testLoadWithCache() : void
    {
        $cache = $this->createMock(CacheInterface::class);

        $cache->expects($this->exactly(3))
            ->method('has')
            ->will($this->returnCallback(function () {
                static $counter = 0;

                return ++$counter > 1;
            }));

        $cache->expects($this->exactly(1))
            ->method('set')
            ->willReturn(null);

        $cache->expects($this->exactly(3))
            ->method('get')
            ->willReturn([
                Fixture\BlankRequestHandler::class => new AnnotationRoute([
                    'name' => 'foo',
                    'path' => '/foo',
                    'methods' => ['GET'],
                ]),
            ]);

        $loader = new AnnotationDirectoryLoader();
        $loader->attach(__DIR__ . '/../Fixture/Annotation/Route/Empty');
        $loader->setCache($cache);

        // attempt to reload annotations...
        $loader->load();
        $loader->load();

        $this->assertCount(1, $loader->load()->all());
    }

    /**
     * @param string $resource
     * @param string $expectedException
     *
     * @return void
     *
     * @dataProvider invalidAnnotatedClassesProvider
     *
     * @runInSeparateProcess
     */
    public function testLoadInvalidAnnotatedClasses(string $resource, string $expectedException) : void
    {
        $loader = new AnnotationDirectoryLoader();
        $loader->attach($resource);

        // the given exception message should be tested through annotation class...
        $this->expectException($expectedException);

        $loader->load();
    }

    /**
     * @return array
     */
    public function invalidAnnotatedClassesProvider() : array
    {
        return [
            [
                __DIR__ . '/../Fixture/Annotation/Route/Invalid/NameMissing',
                InvalidDescriptorArgumentException::class,
            ],
            [
                __DIR__ . '/../Fixture/Annotation/Route/Invalid/NameEmpty',
                InvalidDescriptorArgumentException::class,
            ],
            [
                __DIR__ . '/../Fixture/Annotation/Route/Invalid/NameNotString',
                InvalidDescriptorArgumentException::class,
            ],
            [
                __DIR__ . '/../Fixture/Annotation/Route/Invalid/InvalidHost',
                InvalidDescriptorArgumentException::class,
            ],
            [
                __DIR__ . '/../Fixture/Annotation/Route/Invalid/PathMissing',
                InvalidDescriptorArgumentException::class,
            ],
            [
                __DIR__ . '/../Fixture/Annotation/Route/Invalid/PathEmpty',
                InvalidDescriptorArgumentException::class,
            ],
            [
                __DIR__ . '/../Fixture/Annotation/Route/Invalid/PathNotString',
                InvalidDescriptorArgumentException::class,
            ],
            [
                __DIR__ . '/../Fixture/Annotation/Route/Invalid/MethodsMissing',
                InvalidDescriptorArgumentException::class,
            ],
            [
                __DIR__ . '/../Fixture/Annotation/Route/Invalid/MethodsEmpty',
                InvalidDescriptorArgumentException::class,
            ],
            [
                __DIR__ . '/../Fixture/Annotation/Route/Invalid/MethodsNotArray',
                InvalidDescriptorArgumentException::class,
            ],
            [
                __DIR__ . '/../Fixture/Annotation/Route/Invalid/MethodsNotStringable',
                InvalidDescriptorArgumentException::class,
            ],
            [
                __DIR__ . '/../Fixture/Annotation/Route/Invalid/MiddlewaresNotArray',
                InvalidDescriptorArgumentException::class,
            ],
            [
                __DIR__ . '/../Fixture/Annotation/Route/Invalid/MiddlewaresNotStringable',
                InvalidDescriptorArgumentException::class,
            ],
            [
                __DIR__ . '/../Fixture/Annotation/Route/Invalid/MiddlewaresNotExistable',
                InvalidDescriptorArgumentException::class,
            ],
            [
                __DIR__ . '/../Fixture/Annotation/Route/Invalid/MiddlewaresNotMiddlewarable',
                InvalidDescriptorArgumentException::class,
            ],
            [
                __DIR__ . '/../Fixture/Annotation/Route/Invalid/AttributesNotArray',
                InvalidDescriptorArgumentException::class,
            ],
            [
                __DIR__ . '/../Fixture/Annotation/Route/Invalid/PriorityNotInteger',
                InvalidDescriptorArgumentException::class,
            ],
        ];
    }
}
