<?php declare(strict_types=1);

namespace Sunrise\Http\Router\Tests\Annotation;

/**
 * Import classes
 */
use PHPUnit\Framework\TestCase;
use Sunrise\Http\Router\Annotation\Route as AnnotationRoute;
use Sunrise\Http\Router\Annotation\AnnotationRouteLoader;
use InvalidArgumentException;

/**
 * Import functions
 */
use function class_alias;
use function class_exists;
use function get_class;

/**
 * AnnotationRouteLoaderTest
 */
class AnnotationRouteLoaderTest extends TestCase
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
    public function testAnnotationRouteNameMissing() : void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('@Route.name must be not an empty string.');

        $root = __DIR__ . '/../Fixture/Annotation/AnnotationRouteInvalid';
        (new AnnotationRouteLoader)->discover($root . '/AnnotationRouteNameMissing');
    }

    /**
     * @return void
     */
    public function testAnnotationRouteNameEmpty() : void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('@Route.name must be not an empty string.');

        $root = __DIR__ . '/../Fixture/Annotation/AnnotationRouteInvalid';
        (new AnnotationRouteLoader)->discover($root . '/AnnotationRouteNameEmpty');
    }

    /**
     * @return void
     */
    public function testAnnotationRouteNameNotString() : void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('@Route.name must be not an empty string.');

        $root = __DIR__ . '/../Fixture/Annotation/AnnotationRouteInvalid';
        (new AnnotationRouteLoader)->discover($root . '/AnnotationRouteNameNotString');
    }

    /**
     * @return void
     */
    public function testAnnotationRoutePathMissing() : void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('@Route.path must be not an empty string.');

        $root = __DIR__ . '/../Fixture/Annotation/AnnotationRouteInvalid';
        (new AnnotationRouteLoader)->discover($root . '/AnnotationRoutePathMissing');
    }

    /**
     * @return void
     */
    public function testAnnotationRoutePathEmpty() : void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('@Route.path must be not an empty string.');

        $root = __DIR__ . '/../Fixture/Annotation/AnnotationRouteInvalid';
        (new AnnotationRouteLoader)->discover($root . '/AnnotationRoutePathEmpty');
    }

    /**
     * @return void
     */
    public function testAnnotationRoutePathNotString() : void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('@Route.path must be not an empty string.');

        $root = __DIR__ . '/../Fixture/Annotation/AnnotationRouteInvalid';
        (new AnnotationRouteLoader)->discover($root . '/AnnotationRoutePathNotString');
    }

    /**
     * @return void
     */
    public function testAnnotationRouteMethodsMissing() : void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('@Route.methods must be not an empty array.');

        $root = __DIR__ . '/../Fixture/Annotation/AnnotationRouteInvalid';
        (new AnnotationRouteLoader)->discover($root . '/AnnotationRouteMethodsMissing');
    }

    /**
     * @return void
     */
    public function testAnnotationRouteMethodsEmpty() : void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('@Route.methods must be not an empty array.');

        $root = __DIR__ . '/../Fixture/Annotation/AnnotationRouteInvalid';
        (new AnnotationRouteLoader)->discover($root . '/AnnotationRouteMethodsEmpty');
    }

    /**
     * @return void
     */
    public function testAnnotationRouteMethodsNotArray() : void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('@Route.methods must be not an empty array.');

        $root = __DIR__ . '/../Fixture/Annotation/AnnotationRouteInvalid';
        (new AnnotationRouteLoader)->discover($root . '/AnnotationRouteMethodsNotArray');
    }

    /**
     * @return void
     */
    public function testAnnotationRouteMethodsNotStringable() : void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('@Route.methods must contain only strings.');

        $root = __DIR__ . '/../Fixture/Annotation/AnnotationRouteInvalid';
        (new AnnotationRouteLoader)->discover($root . '/AnnotationRouteMethodsNotStringable');
    }

    /**
     * @return void
     */
    public function testAnnotationRouteMiddlewaresNotArray() : void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('@Route.middlewares must be an array.');

        $root = __DIR__ . '/../Fixture/Annotation/AnnotationRouteInvalid';
        (new AnnotationRouteLoader)->discover($root . '/AnnotationRouteMiddlewaresNotArray');
    }

    /**
     * @return void
     */
    public function testAnnotationRouteMiddlewaresNotStringable() : void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('@Route.middlewares must contain only middlewares.');

        $root = __DIR__ . '/../Fixture/Annotation/AnnotationRouteInvalid';
        (new AnnotationRouteLoader)->discover($root . '/AnnotationRouteMiddlewaresNotStringable');
    }

    /**
     * @return void
     */
    public function testAnnotationRouteMiddlewaresContainNonexistenceClass() : void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('@Route.middlewares must contain only middlewares.');

        $root = __DIR__ . '/../Fixture/Annotation/AnnotationRouteInvalid';
        (new AnnotationRouteLoader)->discover($root . '/AnnotationRouteMiddlewaresContainNonexistenceClass');
    }

    /**
     * @return void
     */
    public function testAnnotationRouteMiddlewaresContainNotMiddleware() : void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('@Route.middlewares must contain only middlewares.');

        $root = __DIR__ . '/../Fixture/Annotation/AnnotationRouteInvalid';
        (new AnnotationRouteLoader)->discover($root . '/AnnotationRouteMiddlewaresContainNotMiddleware');
    }

    /**
     * @return void
     */
    public function testAnnotationRouteAttributesNotArray() : void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('@Route.attributes must be an array.');

        $root = __DIR__ . '/../Fixture/Annotation/AnnotationRouteInvalid';
        (new AnnotationRouteLoader)->discover($root . '/AnnotationRouteAttributesNotArray');
    }

    /**
     * @return void
     */
    public function testAnnotationRoutePriorityNotInteger() : void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('@Route.priority must be an integer.');

        $root = __DIR__ . '/../Fixture/Annotation/AnnotationRouteInvalid';
        (new AnnotationRouteLoader)->discover($root . '/AnnotationRoutePriorityNotInteger');
    }

    /**
     * @return void
     *
     * @todo This test needs to be improved...
     */
    public function testBuildRoutes() : void
    {
        $loader = new AnnotationRouteLoader();
        $loader->discover(__DIR__ . '/../Fixture/Annotation/AnnotationRouteValid');

        $routesMap = [];
        $builtRoutes = $loader->buildRoutes();

        foreach ($builtRoutes as $i => $route) {
            $routesMap[$i]['name'] = $route->getName();
            $routesMap[$i]['path'] = $route->getPath();
            $routesMap[$i]['methods'] = $route->getMethods();
            $routesMap[$i]['requestHandler'] = get_class($route->getRequestHandler());
            $routesMap[$i]['middlewares'] = [];
            $routesMap[$i]['attributes'] = $route->getAttributes();

            foreach ($route->getMiddlewares() as $middleware) {
                $routesMap[$i]['middlewares'][] = get_class($middleware);
            }
        }

        $expectedMap = [
            [
                'name' => 'quuuux',
                'path' => '/quuuux',
                'methods' => ['PATCH', 'DELETE'],
                'requestHandler' => 'Sunrise\Http\Router\Tests\Fixture\Annotation' .
                                    '\AnnotationRouteValid\Subdirectory\QuuuuxRequestHandler',
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
                'requestHandler' => 'Sunrise\Http\Router\Tests\Fixture\Annotation' .
                                    '\AnnotationRouteValid\Subdirectory\QuuuxRequestHandler',
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
                'requestHandler' => 'Sunrise\Http\Router\Tests\Fixture\Annotation' .
                                    '\AnnotationRouteValid\QuuxRequestHandler',
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
                'requestHandler' => 'Sunrise\Http\Router\Tests\Fixture\Annotation' .
                                    '\AnnotationRouteValid\QuxRequestHandler',
                'middlewares' => [
                    'Sunrise\Http\Router\Tests\Fixture\BlankMiddleware',
                    'Sunrise\Http\Router\Tests\Fixture\BlankMiddleware',
                ],
                'attributes' => [
                    'foo' => 'bar',
                    'source' => 'qux',
                ],
            ],
        ];

        $this->assertSame($expectedMap, $routesMap);
    }
}
