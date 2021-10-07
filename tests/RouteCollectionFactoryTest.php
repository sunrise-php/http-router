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
    public function testContracts() : void
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
            new Fixtures\Route(),
            new Fixtures\Route(),
            new Fixtures\Route(),
        ];

        $collection = (new RouteCollectionFactory)->createCollection(...$routes);

        $this->assertSame($routes, $collection->all());
    }
}
