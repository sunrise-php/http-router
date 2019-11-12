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
        if (! class_exists('Route')) {
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
}
