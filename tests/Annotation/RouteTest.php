<?php declare(strict_types=1);

namespace Sunrise\Http\Router\Tests\Annotation;

/**
 * Import classes
 */
use PHPUnit\Framework\TestCase;
use Sunrise\Http\Router\Annotation\Route;
use Sunrise\Http\Router\Exception\InvalidAnnotationParameterException;
use Sunrise\Http\Router\Exception\InvalidAnnotationSourceException;
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
        $this->expectException(InvalidAnnotationParameterException::class);
        $this->expectExceptionMessage('@Route.name must be not an empty string.');

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
        $this->expectException(InvalidAnnotationParameterException::class);
        $this->expectExceptionMessage('@Route.path must be not an empty string.');

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
        $this->expectException(InvalidAnnotationParameterException::class);
        $this->expectExceptionMessage('@Route.methods must be not an empty array.');

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
        $this->expectException(InvalidAnnotationParameterException::class);
        $this->expectExceptionMessage('@Route.name must be not an empty string.');

        new Route([
            'name' => '',
            'path' => '/foo',
            'methods' => ['GET'],
        ]);
    }

    /**
     * @return void
     */
    public function testConstructorParamsContainEmptyPath() : void
    {
        $this->expectException(InvalidAnnotationParameterException::class);
        $this->expectExceptionMessage('@Route.path must be not an empty string.');

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
        $this->expectException(InvalidAnnotationParameterException::class);
        $this->expectExceptionMessage('@Route.methods must be not an empty array.');

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
        $this->expectException(InvalidAnnotationParameterException::class);
        $this->expectExceptionMessage('@Route.name must be not an empty string.');

        new Route([
            'name' => $invalidName,
            'path' => '/foo',
            'methods' => ['GET'],
        ]);
    }

    /**
     * @param mixed $invalidHost
     * @return void
     * @dataProvider invalidDataProviderIfNullOrStringExpected
     * @since 2.6.0
     */
    public function testConstructorParamsContainInvalidHost($invalidHost) : void
    {
        $this->expectException(InvalidAnnotationParameterException::class);
        $this->expectExceptionMessage('@Route.host must be null or string.');

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
        $this->expectException(InvalidAnnotationParameterException::class);
        $this->expectExceptionMessage('@Route.path must be not an empty string.');

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
        $this->expectException(InvalidAnnotationParameterException::class);
        $this->expectExceptionMessage('@Route.methods must be not an empty array.');

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
        $this->expectException(InvalidAnnotationParameterException::class);
        $this->expectExceptionMessage('@Route.middlewares must be an array.');

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
        $this->expectException(InvalidAnnotationParameterException::class);
        $this->expectExceptionMessage('@Route.attributes must be an array.');

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
        $this->expectException(InvalidAnnotationParameterException::class);
        $this->expectExceptionMessage('@Route.summary must be a string.');

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
        $this->expectException(InvalidAnnotationParameterException::class);
        $this->expectExceptionMessage('@Route.description must be an array or a string.');

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
        $this->expectException(InvalidAnnotationParameterException::class);
        $this->expectExceptionMessage('@Route.tags must be an array.');

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
        $this->expectException(InvalidAnnotationParameterException::class);
        $this->expectExceptionMessage('@Route.priority must be an integer.');

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
        $this->expectException(InvalidAnnotationParameterException::class);
        $this->expectExceptionMessage('@Route.methods must contain only strings.');

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
        $this->expectException(InvalidAnnotationParameterException::class);
        $this->expectExceptionMessage('@Route.middlewares must contain only strings.');

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
        $this->expectException(InvalidAnnotationParameterException::class);
        $this->expectExceptionMessage('@Route.middlewares contains a nonexistent or non-middleware class.');

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
        $this->expectException(InvalidAnnotationParameterException::class);
        $this->expectExceptionMessage('@Route.middlewares contains a nonexistent or non-middleware class.');

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
        $this->expectException(InvalidAnnotationParameterException::class);
        $this->expectExceptionMessage('@Route.tags must contain only strings.');

        new Route([
            'name' => 'foo',
            'path' => '/foo',
            'methods' => ['GET'],
            'tags' => [$invalidTag],
        ]);
    }

    /**
     * @return void
     */
    public function testDescriptionConcatenation() : void
    {
        $params = [
            'name' => 'foo',
            'path' => '/foo',
            'methods' => ['GET'],
            'description' => [
                'foo ',
                'bar ',
                'baz ',
                'qux.',
            ],
        ];

        $route = new Route($params);

        $this->assertSame('foo bar baz qux.', $route->description);
    }

    /**
     * @return array
     */
    public function invalidDataProviderIfArrayExpected() : array
    {
        return [
            [null],
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
            [null],
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
            [null],
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
            [null],
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

    /**
     * @return array
     *
     * @since 2.6.0
     */
    public function invalidDataProviderIfNullOrStringExpected() : array
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
}
