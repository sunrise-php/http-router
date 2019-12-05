<?php declare(strict_types=1);

namespace Sunrise\Http\Router\Tests;

/**
 * Import classes
 */
use PHPUnit\Framework\TestCase;
use Sunrise\Http\Router\RouteCollectionFactory;
use Sunrise\Http\Router\RouteCollectionFactoryInterface;
use Sunrise\Http\Router\RouteCollectionInterface;

/**
 * RouteCollectionFactoryTest
 */
class RouteCollectionFactoryTest extends TestCase
{

    /**
     * @return void
     */
    public function testConstructor() : void
    {
        $factory = new RouteCollectionFactory();

        $this->assertInstanceOf(RouteCollectionFactoryInterface::class, $factory);
    }

    /**
     * @return void
     */
    public function testCreateCollection() : void
    {
        $collection = (new RouteCollectionFactory)->createCollection();

        $this->assertInstanceOf(RouteCollectionInterface::class, $collection);
    }

    /**
     * @return void
     */
    public function testCreateCollectionWithRoutes() : void
    {
        $routes = [
            new Fixture\TestRoute(),
            new Fixture\TestRoute(),
            new Fixture\TestRoute(),
        ];

        $collection = (new RouteCollectionFactory)->createCollection(...$routes);

        $this->assertSame($routes, $collection->all());
    }
}
