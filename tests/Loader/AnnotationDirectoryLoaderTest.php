<?php declare(strict_types=1);

namespace Sunrise\Http\Router\Tests\Loader;

/**
 * Import classes
 */
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;
use Psr\SimpleCache\CacheInterface;
use Sunrise\Http\Router\Annotation\Route as AnnotationRoute;
use Sunrise\Http\Router\Exception\InvalidAnnotationParameterException;
use Sunrise\Http\Router\Exception\InvalidAnnotationSourceException;
use Sunrise\Http\Router\Exception\InvalidLoadResourceException;
use Sunrise\Http\Router\Loader\AnnotationDirectoryLoader;
use Sunrise\Http\Router\Loader\LoaderInterface;
use Sunrise\Http\Router\Tests\Fixture;

/**
 * Import functions
 */
use function class_alias;
use function class_exists;

/**
 * AnnotationDirectoryLoaderTest
 */
class AnnotationDirectoryLoaderTest extends TestCase
{

    /**
     * @return void
     */
    public static function setUpBeforeClass() : void
    {
        if (!class_exists('Route')) {
            class_alias(AnnotationRoute::class, 'Route');
        }
    }

    /**
     * @return void
     */
    public function testConstructor() : void
    {
        $loader = new AnnotationDirectoryLoader();

        $this->assertInstanceOf(LoaderInterface::class, $loader);
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

        $this->expectException(InvalidLoadResourceException::class);
        $this->expectExceptionMessage('The "undefined" resource not found.');

        $loader->attach('undefined');
    }

    /**
     * @return void
     */
    public function testLoad() : void
    {
        $container = $this->createMock(ContainerInterface::class);

        $container->expects($this->exactly(12))
            ->method('has')
            ->will($this->returnCallback(function ($class) {
                return Fixture\BlankMiddleware::class === $class;
            }));

        $container->expects($this->exactly(8))
            ->method('get')
            ->will($this->returnCallback(function ($class) {
                return new Fixture\NamedBlankMiddleware('containerize');
            }));

        $loader = new AnnotationDirectoryLoader();
        $loader->attach(__DIR__ . '/../Fixture/Annotation/Route/Valid');
        $loader->setContainer($container);
        $routes = $loader->load();

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
                'Sunrise\Http\Router\Tests\Fixture\NamedBlankMiddleware:containerize',
                'Sunrise\Http\Router\Tests\Fixture\NamedBlankMiddleware:containerize',
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
                'Sunrise\Http\Router\Tests\Fixture\NamedBlankMiddleware:containerize',
                'Sunrise\Http\Router\Tests\Fixture\NamedBlankMiddleware:containerize',
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
                'Sunrise\Http\Router\Tests\Fixture\NamedBlankMiddleware:containerize',
                'Sunrise\Http\Router\Tests\Fixture\NamedBlankMiddleware:containerize',
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
                'Sunrise\Http\Router\Tests\Fixture\NamedBlankMiddleware:containerize',
                'Sunrise\Http\Router\Tests\Fixture\NamedBlankMiddleware:containerize',
            ],
            'attributes' => [
                'foo' => 'bar',
                'bar' => 'baz',
            ],
        ], Fixture\Helper::routesToArray($routes));
    }

    /**
     * @return void
     */
    public function testLoadWithCache() : void
    {
        $cache = $this->createMock(CacheInterface::class);

        $cache->expects($this->exactly(2))
            ->method('has')
            ->will($this->returnCallback(function () {
                static $counter = 0;
                $counter++;

                return $counter > 1;
            }));

        $cache->expects($this->exactly(1))
            ->method('set')
            ->willReturn(null);

        $cache->expects($this->exactly(2))
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
        $loader->load();

        $this->assertCount(1, $loader->load());
    }

    /**
     * @param string $resource
     * @param string $expectedException
     * @param string $expectedExceptionMessage
     *
     * @return void
     *
     * @dataProvider invalidAnnotatedClassesProvider
     */
    public function testLoadInvalidAnnotatedClasses(
        string $resource,
        string $expectedException,
        string $expectedExceptionMessage
    ) : void {
        $loader = new AnnotationDirectoryLoader();
        $loader->attach($resource);

        $this->expectException($expectedException);
        $this->expectExceptionMessage($expectedExceptionMessage);

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
                InvalidAnnotationParameterException::class,
                '@Route.name must be not an empty string.',
            ],
            [
                __DIR__ . '/../Fixture/Annotation/Route/Invalid/NameEmpty',
                InvalidAnnotationParameterException::class,
                '@Route.name must be not an empty string.',
            ],
            [
                __DIR__ . '/../Fixture/Annotation/Route/Invalid/NameNotString',
                InvalidAnnotationParameterException::class,
                '@Route.name must be not an empty string.',
            ],
            [
                __DIR__ . '/../Fixture/Annotation/Route/Invalid/PathMissing',
                InvalidAnnotationParameterException::class,
                '@Route.path must be not an empty string.',
            ],
            [
                __DIR__ . '/../Fixture/Annotation/Route/Invalid/PathEmpty',
                InvalidAnnotationParameterException::class,
                '@Route.path must be not an empty string.',
            ],
            [
                __DIR__ . '/../Fixture/Annotation/Route/Invalid/PathNotString',
                InvalidAnnotationParameterException::class,
                '@Route.path must be not an empty string.',
            ],
            [
                __DIR__ . '/../Fixture/Annotation/Route/Invalid/MethodsMissing',
                InvalidAnnotationParameterException::class,
                '@Route.methods must be not an empty array.',
            ],
            [
                __DIR__ . '/../Fixture/Annotation/Route/Invalid/MethodsEmpty',
                InvalidAnnotationParameterException::class,
                '@Route.methods must be not an empty array.',
            ],
            [
                __DIR__ . '/../Fixture/Annotation/Route/Invalid/MethodsNotArray',
                InvalidAnnotationParameterException::class,
                '@Route.methods must be not an empty array.',
            ],
            [
                __DIR__ . '/../Fixture/Annotation/Route/Invalid/MethodsNotStringable',
                InvalidAnnotationParameterException::class,
                '@Route.methods must contain only strings.',
            ],
            [
                __DIR__ . '/../Fixture/Annotation/Route/Invalid/MiddlewaresNotArray',
                InvalidAnnotationParameterException::class,
                '@Route.middlewares must be an array.',
            ],
            [
                __DIR__ . '/../Fixture/Annotation/Route/Invalid/MiddlewaresNotStringable',
                InvalidAnnotationParameterException::class,
                '@Route.middlewares must contain only strings.',
            ],
            [
                __DIR__ . '/../Fixture/Annotation/Route/Invalid/MiddlewaresNotExistable',
                InvalidAnnotationParameterException::class,
                '@Route.middlewares contains nonexistent class.',
            ],
            [
                __DIR__ . '/../Fixture/Annotation/Route/Invalid/MiddlewaresNotMiddlewarable',
                InvalidAnnotationParameterException::class,
                '@Route.middlewares contains non middleware class.',
            ],
            [
                __DIR__ . '/../Fixture/Annotation/Route/Invalid/AttributesNotArray',
                InvalidAnnotationParameterException::class,
                '@Route.attributes must be an array.',
            ],
            [
                __DIR__ . '/../Fixture/Annotation/Route/Invalid/PriorityNotInteger',
                InvalidAnnotationParameterException::class,
                '@Route.priority must be an integer.',
            ],
            [
                __DIR__ . '/../Fixture/Annotation/Route/Invalid/SourceNotValid',
                InvalidAnnotationSourceException::class,
                '@Route annotation source Sunrise\Http\Router\Tests\Fixture\Annotation\Route\Invalid\SourceNotValid' .
                    ' is not a request handler.',
            ],
        ];
    }
}
