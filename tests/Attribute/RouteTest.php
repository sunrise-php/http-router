<?php declare(strict_types=1);

namespace Sunrise\Http\Router\Tests\Attribute;

/**
 * Import classes
 */
use PHPUnit\Framework\TestCase;
use Sunrise\Http\Router\Attribute\Route;
use Sunrise\Http\Router\Exception\InvalidDescriptorArgumentException;
use Sunrise\Http\Router\RouteDescriptorInterface;
use Sunrise\Http\Router\Tests\Fixture;

/**
 * RouteTest
 *
 * @since 2.6.0
 */
class RouteTest extends TestCase
{

    /**
     * @return void
     */
    public function testConstructor() : void
    {
        $args = [
            'name' => 'foo',
            'host' => null,
            'path' => '/foo',
            'methods' => ['GET'],
        ];

        $route = new Route(
            $args['name'],
            $args['host'],
            $args['path'],
            $args['methods']
        );

        $this->assertInstanceOf(RouteDescriptorInterface::class, $route);

        $this->assertSame($args['name'], $route->getName());
        $this->assertSame($args['host'], $route->getHost());
        $this->assertSame($args['path'], $route->getPath());
        $this->assertSame($args['methods'], $route->getMethods());

        // default properties...
        $this->assertSame([], $route->getMiddlewares());
        $this->assertSame([], $route->getAttributes());
        $this->assertSame('', $route->getSummary());
        $this->assertSame('', $route->getDescription());
        $this->assertSame([], $route->getTags());
        $this->assertSame(0, $route->getPriority());
    }

    /**
     * @return void
     */
    public function testConstructorWithOptionalParams() : void
    {
        $args = [
            'name' => 'foo',
            'host' => 'localhost',
            'path' => '/foo',
            'methods' => ['GET'],
            'middlewares' => [Fixture\BlankMiddleware::class],
            'attributes' => ['foo' => 'bar'],
            'summary' => 'foo summary',
            'description' => 'foo description',
            'tags' => ['foo', 'bar'],
            'priority' => 100,
        ];

        $route = new Route(
            $args['name'],
            $args['host'],
            $args['path'],
            $args['methods'],
            $args['middlewares'],
            $args['attributes'],
            $args['summary'],
            $args['description'],
            $args['tags'],
            $args['priority']
        );

        $this->assertSame($args['name'], $route->getName());
        $this->assertSame($args['host'], $route->getHost());
        $this->assertSame($args['path'], $route->getPath());
        $this->assertSame($args['methods'], $route->getMethods());
        $this->assertSame($args['middlewares'], $route->getMiddlewares());
        $this->assertSame($args['attributes'], $route->getAttributes());
        $this->assertSame($args['summary'], $route->getSummary());
        $this->assertSame($args['description'], $route->getDescription());
        $this->assertSame($args['tags'], $route->getTags());
        $this->assertSame($args['priority'], $route->getPriority());
    }

    /**
     * @return void
     */
    public function testConstructorWithEmptyName() : void
    {
        $this->expectException(InvalidDescriptorArgumentException::class);
        $this->expectExceptionMessage('#[Route.name] must contain a non-empty string.');

        new Route('', null, '/foo', ['GET']);
    }

    /**
     * @return void
     */
    public function testConstructorWithEmptyHost() : void
    {
        $this->expectException(InvalidDescriptorArgumentException::class);
        $this->expectExceptionMessage('#[Route.host] must contain a non-empty string or null.');

        new Route('foo', '', '/foo', ['GET']);
    }

    /**
     * @return void
     */
    public function testConstructorWithEmptyPath() : void
    {
        $this->expectException(InvalidDescriptorArgumentException::class);
        $this->expectExceptionMessage('#[Route.path] must contain a non-empty string.');

        new Route('foo', null, '', ['GET']);
    }

    /**
     * @return void
     */
    public function testConstructorWithEmptyMethods() : void
    {
        $this->expectException(InvalidDescriptorArgumentException::class);
        $this->expectExceptionMessage('#[Route.methods] must contain at least one element.');

        new Route('foo', null, '/foo', []);
    }

    /**
     * @param mixed $invalidMethod
     * @return void
     * @dataProvider invalidDataProviderIfNonEmptyStringExpected
     */
    public function testConstructorWithInvalidMethod($invalidMethod) : void
    {
        $this->expectException(InvalidDescriptorArgumentException::class);
        $this->expectExceptionMessage('#[Route.methods] must contain non-empty strings.');

        new Route('foo', null, '/foo', [$invalidMethod]);
    }

    /**
     * @param mixed $invalidMiddleware
     * @return void
     * @dataProvider invalidDataProviderIfNonEmptyStringExpected
     */
    public function testConstructorWithInvalidMiddleware($invalidMiddleware) : void
    {
        $this->expectException(InvalidDescriptorArgumentException::class);
        $this->expectExceptionMessage('#[Route.middlewares] must contain non-empty strings.');

        new Route('foo', null, '/foo', ['GET'], [$invalidMiddleware]);
    }

    /**
     * @param mixed $invalidTag
     * @return void
     * @dataProvider invalidDataProviderIfNonEmptyStringExpected
     */
    public function testConstructorWithInvalidTag($invalidTag) : void
    {
        $this->expectException(InvalidDescriptorArgumentException::class);
        $this->expectExceptionMessage('#[Route.tags] must contain non-empty strings.');

        new Route('foo', null, '/foo', ['GET'], [], [], '', '', [$invalidTag]);
    }

    /**
     * @return void
     */
    public function testConstructorWithNonexistentMiddleware() : void
    {
        $this->expectException(InvalidDescriptorArgumentException::class);
        $this->expectExceptionMessage('#[Route.middlewares] must contain existing middlewares.');

        new Route('foo', null, '/foo', ['GET'], ['nonexistentClass']);
    }

    /**
     * @return void
     */
    public function testConstructorWithNonMiddlewareMiddleware() : void
    {
        $this->expectException(InvalidDescriptorArgumentException::class);
        $this->expectExceptionMessage('#[Route.middlewares] must contain existing middlewares.');

        new Route('foo', null, '/foo', ['GET'], [stdClass::class]);
    }

    /**
     * @return array
     */
    public function invalidDataProviderIfNonEmptyStringExpected() : array
    {
        return [
            [null],
            [true],
            [false],
            [0],
            [0.0],
            [''],
            [[]],
            [new \stdClass],
            [function () {
            }],
            [\STDOUT],
        ];
    }
}
