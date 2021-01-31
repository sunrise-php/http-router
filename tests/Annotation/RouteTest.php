<?php declare(strict_types=1);

namespace Sunrise\Http\Router\Tests\Annotation;

/**
 * Import classes
 */
use PHPUnit\Framework\TestCase;
use Sunrise\Http\Router\Annotation\Route;
use Sunrise\Http\Router\Exception\InvalidDescriptorArgumentException;
use Sunrise\Http\Router\RouteDescriptorInterface;
use Sunrise\Http\Router\Tests\Fixture;

/**
 * RouteTest
 */
class RouteTest extends TestCase
{

    /**
     * @return void
     */
    public function testConstructor() : void
    {
        $params = [
            'name' => 'foo',
            'path' => '/foo',
            'methods' => ['GET'],
        ];

        $route = new Route($params);

        $this->assertInstanceOf(RouteDescriptorInterface::class, $route);

        $this->assertSame($params['name'], $route->getName());
        $this->assertSame($params['path'], $route->getPath());
        $this->assertSame($params['methods'], $route->getMethods());

        // default property values...
        $this->assertSame(null, $route->getHost());
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
        $params = [
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

        $route = new Route($params);

        $this->assertSame($params['name'], $route->getName());
        $this->assertSame($params['host'], $route->getHost());
        $this->assertSame($params['path'], $route->getPath());
        $this->assertSame($params['methods'], $route->getMethods());
        $this->assertSame($params['middlewares'], $route->getMiddlewares());
        $this->assertSame($params['attributes'], $route->getAttributes());
        $this->assertSame($params['summary'], $route->getSummary());
        $this->assertSame($params['description'], $route->getDescription());
        $this->assertSame($params['tags'], $route->getTags());
        $this->assertSame($params['priority'], $route->getPriority());
    }

    /**
     * @return void
     *
     * @since 2.6.0
     */
    public function testConstructorWithNullableHost() : void
    {
        $params = [
            'name' => 'foo',
            'host' => null,
            'path' => '/foo',
            'methods' => ['GET'],
        ];

        $route = new Route($params);

        $this->assertSame($params['name'], $route->getName());
        $this->assertSame($params['host'], $route->getHost());
        $this->assertSame($params['path'], $route->getPath());
        $this->assertSame($params['methods'], $route->getMethods());
    }

    /**
     * @return void
     */
    public function testConstructorParamsNotContainName() : void
    {
        $this->expectException(InvalidDescriptorArgumentException::class);
        $this->expectExceptionMessage('@Route.name must contain a non-empty string.');

        new Route([
            'path' => '/foo',
            'methods' => ['GET'],
        ]);
    }

    /**
     * @return void
     */
    public function testConstructorParamsNotContainPath() : void
    {
        $this->expectException(InvalidDescriptorArgumentException::class);
        $this->expectExceptionMessage('@Route.path must contain a non-empty string.');

        new Route([
            'name' => 'foo',
            'methods' => ['GET'],
        ]);
    }

    /**
     * @return void
     */
    public function testConstructorParamsNotContainMethods() : void
    {
        $this->expectException(InvalidDescriptorArgumentException::class);
        $this->expectExceptionMessage('@Route.methods must contain a non-empty array.');

        new Route([
            'name' => 'foo',
            'path' => '/foo',
        ]);
    }

    /**
     * @return void
     */
    public function testConstructorParamsContainEmptyName() : void
    {
        $this->expectException(InvalidDescriptorArgumentException::class);
        $this->expectExceptionMessage('@Route.name must contain a non-empty string.');

        new Route([
            'name' => '',
            'path' => '/foo',
            'methods' => ['GET'],
        ]);
    }

    /**
     * @return void
     */
    public function testConstructorParamsContainEmptyHost() : void
    {
        $this->expectException(InvalidDescriptorArgumentException::class);
        $this->expectExceptionMessage('@Route.host must contain a non-empty string.');

        new Route([
            'name' => 'foo',
            'host' => '',
            'path' => '/foo',
            'methods' => ['GET'],
        ]);
    }

    /**
     * @return void
     */
    public function testConstructorParamsContainEmptyPath() : void
    {
        $this->expectException(InvalidDescriptorArgumentException::class);
        $this->expectExceptionMessage('@Route.path must contain a non-empty string.');

        new Route([
            'name' => 'foo',
            'path' => '',
            'methods' => ['GET'],
        ]);
    }

    /**
     * @return void
     */
    public function testConstructorParamsContainEmptyMethods() : void
    {
        $this->expectException(InvalidDescriptorArgumentException::class);
        $this->expectExceptionMessage('@Route.methods must contain a non-empty array.');

        new Route([
            'name' => 'foo',
            'path' => '/foo',
            'methods' => [],
        ]);
    }

    /**
     * @param mixed $invalidName
     * @return void
     * @dataProvider invalidDataProviderIfStringExpected
     */
    public function testConstructorParamsContainInvalidName($invalidName) : void
    {
        $this->expectException(InvalidDescriptorArgumentException::class);
        $this->expectExceptionMessage('@Route.name must contain a non-empty string.');

        new Route([
            'name' => $invalidName,
            'path' => '/foo',
            'methods' => ['GET'],
        ]);
    }

    /**
     * @param mixed $invalidHost
     * @return void
     * @dataProvider invalidDataProviderIfStringExpected
     * @since 2.6.0
     */
    public function testConstructorParamsContainInvalidHost($invalidHost) : void
    {
        $this->expectException(InvalidDescriptorArgumentException::class);
        $this->expectExceptionMessage('@Route.host must contain a non-empty string.');

        new Route([
            'name' => 'foo',
            'host' => $invalidHost,
            'path' => '/foo',
            'methods' => ['GET'],
        ]);
    }

    /**
     * @param mixed $invalidPath
     * @return void
     * @dataProvider invalidDataProviderIfStringExpected
     */
    public function testConstructorParamsContainInvalidPath($invalidPath) : void
    {
        $this->expectException(InvalidDescriptorArgumentException::class);
        $this->expectExceptionMessage('@Route.path must contain a non-empty string.');

        new Route([
            'name' => 'foo',
            'path' => $invalidPath,
            'methods' => ['GET'],
        ]);
    }

    /**
     * @param mixed $invalidMethods
     * @return void
     * @dataProvider invalidDataProviderIfArrayExpected
     */
    public function testConstructorParamsContainInvalidMethods($invalidMethods) : void
    {
        $this->expectException(InvalidDescriptorArgumentException::class);
        $this->expectExceptionMessage('@Route.methods must contain a non-empty array.');

        new Route([
            'name' => 'foo',
            'path' => '/foo',
            'methods' => $invalidMethods,
        ]);
    }

    /**
     * @param mixed $invalidMiddlewares
     * @return void
     * @dataProvider invalidDataProviderIfArrayExpected
     */
    public function testConstructorParamsContainInvalidMiddlewares($invalidMiddlewares) : void
    {
        $this->expectException(InvalidDescriptorArgumentException::class);
        $this->expectExceptionMessage('@Route.middlewares must contain an array.');

        new Route([
            'name' => 'foo',
            'path' => '/foo',
            'methods' => ['GET'],
            'middlewares' => $invalidMiddlewares,
        ]);
    }

    /**
     * @param mixed $invalidAttributes
     * @return void
     * @dataProvider invalidDataProviderIfArrayExpected
     */
    public function testConstructorParamsContainInvalidAttributes($invalidAttributes) : void
    {
        $this->expectException(InvalidDescriptorArgumentException::class);
        $this->expectExceptionMessage('@Route.attributes must contain an array.');

        new Route([
            'name' => 'foo',
            'path' => '/foo',
            'methods' => ['GET'],
            'attributes' => $invalidAttributes,
        ]);
    }

    /**
     * @param mixed $invalidSummary
     * @return void
     * @dataProvider invalidDataProviderIfStringExpected
     */
    public function testConstructorParamsContainInvalidSummary($invalidSummary) : void
    {
        $this->expectException(InvalidDescriptorArgumentException::class);
        $this->expectExceptionMessage('@Route.summary must contain a string.');

        new Route([
            'name' => 'foo',
            'path' => '/foo',
            'methods' => ['GET'],
            'summary' => $invalidSummary,
        ]);
    }

    /**
     * @param mixed $invalidDescription
     * @return void
     * @dataProvider invalidDataProviderIfArrayOrStringExpected
     */
    public function testConstructorParamsContainInvalidDescription($invalidDescription) : void
    {
        $this->expectException(InvalidDescriptorArgumentException::class);
        $this->expectExceptionMessage('@Route.description must contain a string.');

        new Route([
            'name' => 'foo',
            'path' => '/foo',
            'methods' => ['GET'],
            'description' => $invalidDescription,
        ]);
    }

    /**
     * @param mixed $invalidTags
     * @return void
     * @dataProvider invalidDataProviderIfArrayExpected
     */
    public function testConstructorParamsContainInvalidTags($invalidTags) : void
    {
        $this->expectException(InvalidDescriptorArgumentException::class);
        $this->expectExceptionMessage('@Route.tags must contain an array.');

        new Route([
            'name' => 'foo',
            'path' => '/foo',
            'methods' => ['GET'],
            'tags' => $invalidTags,
        ]);
    }

    /**
     * @param mixed $invalidPriority
     * @return void
     * @dataProvider invalidDataProviderIfIntegerExpected
     */
    public function testConstructorParamsContainInvalidPriority($invalidPriority) : void
    {
        $this->expectException(InvalidDescriptorArgumentException::class);
        $this->expectExceptionMessage('@Route.priority must contain an integer.');

        new Route([
            'name' => 'foo',
            'path' => '/foo',
            'methods' => ['GET'],
            'priority' => $invalidPriority,
        ]);
    }

    /**
     * @param mixed $invalidMethod
     * @return void
     * @dataProvider invalidDataProviderIfStringExpected
     */
    public function testConstructorMethodsParamContainsInvalidValue($invalidMethod) : void
    {
        $this->expectException(InvalidDescriptorArgumentException::class);
        $this->expectExceptionMessage('@Route.methods must contain non-empty strings.');

        new Route([
            'name' => 'foo',
            'path' => '/foo',
            'methods' => [$invalidMethod],
        ]);
    }

    /**
     * @param mixed $invalidMiddleware
     * @return void
     * @dataProvider invalidDataProviderIfStringExpected
     */
    public function testConstructorMiddlewaresParamContainsInvalidValue($invalidMiddleware) : void
    {
        $this->expectException(InvalidDescriptorArgumentException::class);
        $this->expectExceptionMessage('@Route.middlewares must contain the class names of existing middlewares.');

        new Route([
            'name' => 'foo',
            'path' => '/foo',
            'methods' => ['GET'],
            'middlewares' => [$invalidMiddleware],
        ]);
    }

    /**
     * @return void
     */
    public function testConstructorMiddlewaresParamContainsNonexistentClass() : void
    {
        $this->expectException(InvalidDescriptorArgumentException::class);
        $this->expectExceptionMessage('@Route.middlewares must contain the class names of existing middlewares.');

        new Route([
            'name' => 'foo',
            'path' => '/foo',
            'methods' => ['GET'],
            'middlewares' => ['nonexistentClass'],
        ]);
    }

    /**
     * @return void
     */
    public function testConstructorMiddlewaresParamContainsNonMiddlewareClass() : void
    {
        $this->expectException(InvalidDescriptorArgumentException::class);
        $this->expectExceptionMessage('@Route.middlewares must contain the class names of existing middlewares.');

        new Route([
            'name' => 'foo',
            'path' => '/foo',
            'methods' => ['GET'],
            'middlewares' => ['stdClass'],
        ]);
    }

    /**
     * @param mixed $invalidTag
     * @return void
     * @dataProvider invalidDataProviderIfStringExpected
     */
    public function testConstructorTagsParamContainsInvalidValue($invalidTag) : void
    {
        $this->expectException(InvalidDescriptorArgumentException::class);
        $this->expectExceptionMessage('@Route.tags must contain non-empty strings.');

        new Route([
            'name' => 'foo',
            'path' => '/foo',
            'methods' => ['GET'],
            'tags' => [$invalidTag],
        ]);
    }

    /**
     * @return array
     */
    public function invalidDataProviderIfArrayExpected() : array
    {
        return [
            [true],
            [false],
            [0],
            [0.0],
            [''],
            [new \stdClass],
            [function () {
            }],
            [\STDOUT],
        ];
    }

    /**
     * @return array
     */
    public function invalidDataProviderIfIntegerExpected() : array
    {
        return [
            [true],
            [false],
            [0.0],
            [''],
            [[]],
            [new \stdClass],
            [function () {
            }],
            [\STDOUT],
        ];
    }

    /**
     * @return array
     */
    public function invalidDataProviderIfStringExpected() : array
    {
        return [
            [true],
            [false],
            [0],
            [0.0],
            [[]],
            [new \stdClass],
            [function () {
            }],
            [\STDOUT],
        ];
    }

    /**
     * @return array
     */
    public function invalidDataProviderIfArrayOrStringExpected() : array
    {
        return [
            [true],
            [false],
            [0],
            [0.0],
            [new \stdClass],
            [function () {
            }],
            [\STDOUT],
        ];
    }
}
