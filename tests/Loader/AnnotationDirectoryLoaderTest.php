<?php declare(strict_types=1);

namespace Sunrise\Http\Router\Tests\Loader;

/**
 * Import classes
 */
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;
use Sunrise\Http\Router\Annotation\Route as AnnotationRoute;
use Sunrise\Http\Router\Exception\InvalidAnnotationParameterException;
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
    public function testAnnotationRouteNameMissing() : void
    {
        $this->expectException(InvalidAnnotationParameterException::class);
        $this->expectExceptionMessage('@Route.name must be not an empty string.');

        $destination = __DIR__ . '/../Fixture/Annotation/AnnotationRouteInvalid';
        (new AnnotationDirectoryLoader)->load($destination . '/AnnotationRouteNameMissing');
    }

    /**
     * @return void
     */
    public function testAnnotationRouteNameEmpty() : void
    {
        $this->expectException(InvalidAnnotationParameterException::class);
        $this->expectExceptionMessage('@Route.name must be not an empty string.');

        $destination = __DIR__ . '/../Fixture/Annotation/AnnotationRouteInvalid';
        (new AnnotationDirectoryLoader)->load($destination . '/AnnotationRouteNameEmpty');
    }

    /**
     * @return void
     */
    public function testAnnotationRouteNameNotString() : void
    {
        $this->expectException(InvalidAnnotationParameterException::class);
        $this->expectExceptionMessage('@Route.name must be not an empty string.');

        $destination = __DIR__ . '/../Fixture/Annotation/AnnotationRouteInvalid';
        (new AnnotationDirectoryLoader)->load($destination . '/AnnotationRouteNameNotString');
    }

    /**
     * @return void
     */
    public function testAnnotationRoutePathMissing() : void
    {
        $this->expectException(InvalidAnnotationParameterException::class);
        $this->expectExceptionMessage('@Route.path must be not an empty string.');

        $destination = __DIR__ . '/../Fixture/Annotation/AnnotationRouteInvalid';
        (new AnnotationDirectoryLoader)->load($destination . '/AnnotationRoutePathMissing');
    }

    /**
     * @return void
     */
    public function testAnnotationRoutePathEmpty() : void
    {
        $this->expectException(InvalidAnnotationParameterException::class);
        $this->expectExceptionMessage('@Route.path must be not an empty string.');

        $destination = __DIR__ . '/../Fixture/Annotation/AnnotationRouteInvalid';
        (new AnnotationDirectoryLoader)->load($destination . '/AnnotationRoutePathEmpty');
    }

    /**
     * @return void
     */
    public function testAnnotationRoutePathNotString() : void
    {
        $this->expectException(InvalidAnnotationParameterException::class);
        $this->expectExceptionMessage('@Route.path must be not an empty string.');

        $destination = __DIR__ . '/../Fixture/Annotation/AnnotationRouteInvalid';
        (new AnnotationDirectoryLoader)->load($destination . '/AnnotationRoutePathNotString');
    }

    /**
     * @return void
     */
    public function testAnnotationRouteMethodsMissing() : void
    {
        $this->expectException(InvalidAnnotationParameterException::class);
        $this->expectExceptionMessage('@Route.methods must be not an empty array.');

        $destination = __DIR__ . '/../Fixture/Annotation/AnnotationRouteInvalid';
        (new AnnotationDirectoryLoader)->load($destination . '/AnnotationRouteMethodsMissing');
    }

    /**
     * @return void
     */
    public function testAnnotationRouteMethodsEmpty() : void
    {
        $this->expectException(InvalidAnnotationParameterException::class);
        $this->expectExceptionMessage('@Route.methods must be not an empty array.');

        $destination = __DIR__ . '/../Fixture/Annotation/AnnotationRouteInvalid';
        (new AnnotationDirectoryLoader)->load($destination . '/AnnotationRouteMethodsEmpty');
    }

    /**
     * @return void
     */
    public function testAnnotationRouteMethodsNotArray() : void
    {
        $this->expectException(InvalidAnnotationParameterException::class);
        $this->expectExceptionMessage('@Route.methods must be not an empty array.');

        $destination = __DIR__ . '/../Fixture/Annotation/AnnotationRouteInvalid';
        (new AnnotationDirectoryLoader)->load($destination . '/AnnotationRouteMethodsNotArray');
    }

    /**
     * @return void
     */
    public function testAnnotationRouteMethodsNotStringable() : void
    {
        $this->expectException(InvalidAnnotationParameterException::class);
        $this->expectExceptionMessage('@Route.methods must contain only strings.');

        $destination = __DIR__ . '/../Fixture/Annotation/AnnotationRouteInvalid';
        (new AnnotationDirectoryLoader)->load($destination . '/AnnotationRouteMethodsNotStringable');
    }

    /**
     * @return void
     */
    public function testAnnotationRouteMiddlewaresNotArray() : void
    {
        $this->expectException(InvalidAnnotationParameterException::class);
        $this->expectExceptionMessage('@Route.middlewares must be an array.');

        $destination = __DIR__ . '/../Fixture/Annotation/AnnotationRouteInvalid';
        (new AnnotationDirectoryLoader)->load($destination . '/AnnotationRouteMiddlewaresNotArray');
    }

    /**
     * @return void
     */
    public function testAnnotationRouteMiddlewaresNotStringable() : void
    {
        $this->expectException(InvalidAnnotationParameterException::class);
        $this->expectExceptionMessage('@Route.middlewares must contain only strings.');

        $destination = __DIR__ . '/../Fixture/Annotation/AnnotationRouteInvalid';
        (new AnnotationDirectoryLoader)->load($destination . '/AnnotationRouteMiddlewaresNotStringable');
    }

    /**
     * @return void
     */
    public function testAnnotationRouteMiddlewaresContainNonexistenceClass() : void
    {
        $this->expectException(InvalidAnnotationParameterException::class);
        $this->expectExceptionMessage('@Route.middlewares contains nonexistent class.');

        $destination = __DIR__ . '/../Fixture/Annotation/AnnotationRouteInvalid';
        (new AnnotationDirectoryLoader)->load($destination . '/AnnotationRouteMiddlewaresContainNonexistenceClass');
    }

    /**
     * @return void
     */
    public function testAnnotationRouteMiddlewaresContainNotMiddleware() : void
    {
        $this->expectException(InvalidAnnotationParameterException::class);
        $this->expectExceptionMessage('@Route.middlewares contains non middleware class.');

        $destination = __DIR__ . '/../Fixture/Annotation/AnnotationRouteInvalid';
        (new AnnotationDirectoryLoader)->load($destination . '/AnnotationRouteMiddlewaresContainNotMiddleware');
    }

    /**
     * @return void
     */
    public function testAnnotationRouteAttributesNotArray() : void
    {
        $this->expectException(InvalidAnnotationParameterException::class);
        $this->expectExceptionMessage('@Route.attributes must be an array.');

        $destination = __DIR__ . '/../Fixture/Annotation/AnnotationRouteInvalid';
        (new AnnotationDirectoryLoader)->load($destination . '/AnnotationRouteAttributesNotArray');
    }

    /**
     * @return void
     */
    public function testAnnotationRoutePriorityNotInteger() : void
    {
        $this->expectException(InvalidAnnotationParameterException::class);
        $this->expectExceptionMessage('@Route.priority must be an integer.');

        $destination = __DIR__ . '/../Fixture/Annotation/AnnotationRouteInvalid';
        (new AnnotationDirectoryLoader)->load($destination . '/AnnotationRoutePriorityNotInteger');
    }

    /**
     * @return void
     */
    public function testLoad() : void
    {
        $loader = new AnnotationDirectoryLoader();
        $routes = $loader->load(__DIR__ . '/../Fixture/Annotation/AnnotationRouteValid/ForTestLoad');

        $this->assertSame([
            [
                'name' => 'quuuux',
                'path' => '/quuuux',
                'methods' => ['PATCH', 'DELETE'],
                'requestHandler' => 'Sunrise\Http\Router\Tests\Fixture\Annotation\AnnotationRouteValid' .
                                    '\ForTestLoad\Subdirectory\QuuuuxRequestHandler',
                'middlewares' => [
                    'Sunrise\Http\Router\Tests\Fixture\BlankMiddleware',
                    'Sunrise\Http\Router\Tests\Fixture\BlankMiddleware',
                ],
                'attributes' => [
                    'foo' => 'bar',
                    'source' => 'quuuux',
                ],
            ],
            [
                'name' => 'quuux',
                'path' => '/quuux',
                'methods' => ['PUT', 'PATCH'],
                'requestHandler' => 'Sunrise\Http\Router\Tests\Fixture\Annotation\AnnotationRouteValid' .
                                    '\ForTestLoad\Subdirectory\QuuuxRequestHandler',
                'middlewares' => [
                    'Sunrise\Http\Router\Tests\Fixture\BlankMiddleware',
                    'Sunrise\Http\Router\Tests\Fixture\BlankMiddleware',
                ],
                'attributes' => [
                    'foo' => 'bar',
                    'source' => 'quuux',
                ],
            ],
            [
                'name' => 'quux',
                'path' => '/quux',
                'methods' => ['POST', 'PUT'],
                'requestHandler' => 'Sunrise\Http\Router\Tests\Fixture\Annotation\AnnotationRouteValid' .
                                    '\ForTestLoad\QuuxRequestHandler',
                'middlewares' => [
                    'Sunrise\Http\Router\Tests\Fixture\BlankMiddleware',
                    'Sunrise\Http\Router\Tests\Fixture\BlankMiddleware',
                ],
                'attributes' => [
                    'foo' => 'bar',
                    'source' => 'quux',
                ],
            ],
            [
                'name' => 'qux',
                'path' => '/qux',
                'methods' => ['GET', 'POST'],
                'requestHandler' => 'Sunrise\Http\Router\Tests\Fixture\Annotation\AnnotationRouteValid' .
                                    '\ForTestLoad\QuxRequestHandler',
                'middlewares' => [
                    'Sunrise\Http\Router\Tests\Fixture\BlankMiddleware',
                    'Sunrise\Http\Router\Tests\Fixture\BlankMiddleware',
                ],
                'attributes' => [
                    'foo' => 'bar',
                    'source' => 'qux',
                ],
            ],
        ], Fixture\Helper::routesToArray($routes));
    }

    /**
     * @return void
     */
    public function testLoadWithContainer() : void
    {
        $container = $this->createMock(ContainerInterface::class);

        $container->expects($this->any())->method('has')->will($this->returnCallback(function ($class) {
            return Fixture\BlankMiddleware::class === $class;
        }));

        $container->expects($this->any())->method('get')->will($this->returnCallback(function ($class) {
            return new Fixture\NamedBlankMiddleware('fromContainer');
        }));

        $loader = new AnnotationDirectoryLoader();
        $loader->setContainer($container);
        $routes = $loader->load(__DIR__ . '/../Fixture/Annotation/AnnotationRouteValid/ForTestLoadWithContainer');

        $this->assertSame([
            [
                'name' => 'foo',
                'path' => '/foo',
                'methods' => ['GET'],
                'requestHandler' => 'Sunrise\Http\Router\Tests\Fixture\Annotation\AnnotationRouteValid' .
                                    '\ForTestLoadWithContainer\FooRequestHandler',
                'middlewares' => [
                    'Sunrise\Http\Router\Tests\Fixture\NamedBlankMiddleware:fromContainer',
                ],
                'attributes' => [
                ],
            ],
        ], Fixture\Helper::routesToArray($routes));
    }
}
