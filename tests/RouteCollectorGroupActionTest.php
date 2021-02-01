<?php declare(strict_types=1);

namespace Sunrise\Http\Router\Tests;

/**
 * Import classes
 */
use PHPUnit\Framework\TestCase;
use Sunrise\Http\Router\RouteCollection;
use Sunrise\Http\Router\RouteCollectorGroupAction;

/**
 * Import functions
 */
use function array_merge;

/**
 * RouteCollectorGroupActionTest
 */
class RouteCollectorGroupActionTest extends TestCase
{

    /**
     * @return void
     *
     * @since 2.6.0
     */
    public function testHost() : void
    {
        $routes = [
            new Fixture\TestRoute(),
            new Fixture\TestRoute(),
            new Fixture\TestRoute(),
        ];

        $collection = new RouteCollection();
        $collection->add(...$routes);

        $groupAction = new RouteCollectorGroupAction($collection);
        $groupAction->setHost('google.com');

        $this->assertSame('google.com', $routes[0]->getHost());
        $this->assertSame('google.com', $routes[1]->getHost());
        $this->assertSame('google.com', $routes[2]->getHost());
    }

    /**
     * @return void
     */
    public function testAddPrefix() : void
    {
        $routes = [
            new Fixture\TestRoute(),
            new Fixture\TestRoute(),
            new Fixture\TestRoute(),
        ];

        $routes[0]->setPath('/foo');
        $routes[1]->setPath('/bar');
        $routes[2]->setPath('/baz');

        $collection = new RouteCollection();
        $collection->add(...$routes);

        $groupAction = new RouteCollectorGroupAction($collection);
        $groupAction->addPrefix('/api');

        $this->assertSame('/api/foo', $routes[0]->getPath());
        $this->assertSame('/api/bar', $routes[1]->getPath());
        $this->assertSame('/api/baz', $routes[2]->getPath());
    }

    /**
     * @return void
     */
    public function testAddSuffix() : void
    {
        $routes = [
            new Fixture\TestRoute(),
            new Fixture\TestRoute(),
            new Fixture\TestRoute(),
        ];

        $routes[0]->setPath('/foo');
        $routes[1]->setPath('/bar');
        $routes[2]->setPath('/baz');

        $collection = new RouteCollection();
        $collection->add(...$routes);

        $groupAction = new RouteCollectorGroupAction($collection);
        $groupAction->addSuffix('.json');

        $this->assertSame('/foo.json', $routes[0]->getPath());
        $this->assertSame('/bar.json', $routes[1]->getPath());
        $this->assertSame('/baz.json', $routes[2]->getPath());
    }

    /**
     * @return void
     */
    public function testAddMethod() : void
    {
        $routes = [
            new Fixture\TestRoute(),
            new Fixture\TestRoute(),
            new Fixture\TestRoute(),
        ];

        $routes[0]->setMethods('FOO');
        $routes[1]->setMethods('BAR');
        $routes[2]->setMethods('BAZ');

        $collection = new RouteCollection();
        $collection->add(...$routes);

        $groupAction = new RouteCollectorGroupAction($collection);
        $groupAction->addMethod('QUX', 'QUUX');

        $this->assertSame(['FOO', 'QUX', 'QUUX'], $routes[0]->getMethods());
        $this->assertSame(['BAR', 'QUX', 'QUUX'], $routes[1]->getMethods());
        $this->assertSame(['BAZ', 'QUX', 'QUUX'], $routes[2]->getMethods());
    }

    /**
     * @return void
     */
    public function testAddMiddleware() : void
    {
        $routes = [
            new Fixture\TestRoute(),
            new Fixture\TestRoute(),
            new Fixture\TestRoute(),
        ];

        $newMiddlewares = [
            new Fixture\NamedBlankMiddleware('foo'),
            new Fixture\NamedBlankMiddleware('bar'),
            new Fixture\NamedBlankMiddleware('baz'),
        ];

        $additionalMiddlewares = [
            new Fixture\NamedBlankMiddleware('qux'),
            new Fixture\NamedBlankMiddleware('quux'),
            new Fixture\NamedBlankMiddleware('quuux'),
        ];

        $routes[0]->setMiddlewares(...$newMiddlewares);
        $routes[1]->setMiddlewares(...$newMiddlewares);
        $routes[2]->setMiddlewares(...$newMiddlewares);

        $collection = new RouteCollection();
        $collection->add(...$routes);

        $groupAction = new RouteCollectorGroupAction($collection);
        $groupAction->addMiddleware(...$additionalMiddlewares);

        $this->assertSame(array_merge($newMiddlewares, $additionalMiddlewares), $routes[0]->getMiddlewares());
        $this->assertSame(array_merge($newMiddlewares, $additionalMiddlewares), $routes[1]->getMiddlewares());
        $this->assertSame(array_merge($newMiddlewares, $additionalMiddlewares), $routes[2]->getMiddlewares());
    }

    /**
     * @return void
     */
    public function testUnshiftMiddleware() : void
    {
        $routes = [
            new Fixture\TestRoute(),
            new Fixture\TestRoute(),
            new Fixture\TestRoute(),
        ];

        $newMiddlewares = [
            new Fixture\NamedBlankMiddleware('foo'),
            new Fixture\NamedBlankMiddleware('bar'),
            new Fixture\NamedBlankMiddleware('baz'),
        ];

        $additionalMiddlewares = [
            new Fixture\NamedBlankMiddleware('qux'),
            new Fixture\NamedBlankMiddleware('quux'),
            new Fixture\NamedBlankMiddleware('quuux'),
        ];

        $routes[0]->setMiddlewares(...$newMiddlewares);
        $routes[1]->setMiddlewares(...$newMiddlewares);
        $routes[2]->setMiddlewares(...$newMiddlewares);

        $collection = new RouteCollection();
        $collection->add(...$routes);

        $groupAction = new RouteCollectorGroupAction($collection);
        $groupAction->unshiftMiddleware(...$additionalMiddlewares);

        $this->assertSame(array_merge($additionalMiddlewares, $newMiddlewares), $routes[0]->getMiddlewares());
        $this->assertSame(array_merge($additionalMiddlewares, $newMiddlewares), $routes[1]->getMiddlewares());
        $this->assertSame(array_merge($additionalMiddlewares, $newMiddlewares), $routes[2]->getMiddlewares());
    }
}
