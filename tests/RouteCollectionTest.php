<?php declare(strict_types=1);

namespace Sunrise\Http\Router\Tests;

/**
 * Import classes
 */
use PHPUnit\Framework\TestCase;
use Sunrise\Http\Router\RouteCollection;
use Sunrise\Http\Router\RouteCollectionInterface;
use ArrayIterator;
use IteratorAggregate;
use Countable;

/**
 * RouteCollectionTest
 */
class RouteCollectionTest extends TestCase
{

    /**
     * @return void
     */
    public function testConstructor() : void
    {
        $collection = new RouteCollection();

        $this->assertInstanceOf(RouteCollectionInterface::class, $collection);
        $this->assertInstanceOf(IteratorAggregate::class, $collection);
        $this->assertInstanceOf(Countable::class, $collection);
    }

    /**
     * @return void
     */
    public function testConstructorWithRoutes() : void
    {
        $routes = [
            new Fixture\TestRoute(),
            new Fixture\TestRoute(),
            new Fixture\TestRoute(),
        ];

        $collection = new RouteCollection(...$routes);

        $this->assertSame($routes, $collection->all());
    }

    /**
     * @return void
     */
    public function testAdd() : void
    {
        $routes = [
            new Fixture\TestRoute(),
            new Fixture\TestRoute(),
            new Fixture\TestRoute(),
        ];

        $collection = new RouteCollection();

        $collection->add(...$routes);

        $this->assertSame($routes, $collection->all());
    }

    /**
     * @return void
     */
    public function testCount() : void
    {
        $routes = [
            new Fixture\TestRoute(),
            new Fixture\TestRoute(),
            new Fixture\TestRoute(),
        ];

        $collection = new RouteCollection();

        $this->assertCount(0, $collection);

        $collection->add(...$routes);

        $this->assertCount(3, $collection);
    }

    /**
     * @return void
     */
    public function testIterator() : void
    {
        $collection = new RouteCollection();

        $this->assertInstanceOf(ArrayIterator::class, $collection->getIterator());
    }
}
