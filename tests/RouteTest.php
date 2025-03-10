<?php

declare(strict_types=1);

namespace Sunrise\Http\Router\Tests;

use PHPUnit\Framework\TestCase;
use Sunrise\Http\Router\Route;

final class RouteTest extends TestCase
{
    use TestKit;

    public function testConstructor(): void
    {
        $json = $this->mockMediaType('application/json');

        $route = new Route(
            name: 'foo',
            path: '/foo',
            requestHandler: '@requestHandler',
            patterns: ['id' => '\d+'],
            methods: ['GET'],
            attributes: ['id' => '1'],
            middlewares: ['@middleware'],
            consumes: [$json],
            produces: [$json],
            tags: ['tag'],
            summary: 'summary',
            description: 'description',
            isDeprecated: true,
            isApiRoute: true,
            pattern: '#^/foo$#uD',
        );

        self::assertSame('foo', $route->getName());
        self::assertSame('/foo', $route->getPath());
        self::assertSame('@requestHandler', $route->getRequestHandler());
        self::assertSame(['id' => '\d+'], $route->getPatterns());
        self::assertSame(['GET'], $route->getMethods());
        self::assertSame(['id' => '1'], $route->getAttributes());
        self::assertSame(['@middleware'], $route->getMiddlewares());
        self::assertSame([$json], $route->getConsumedMediaTypes());
        self::assertSame([$json], $route->getProducedMediaTypes());
        self::assertSame(['tag'], $route->getTags());
        self::assertSame('summary', $route->getSummary());
        self::assertSame('description', $route->getDescription());
        self::assertSame(true, $route->isDeprecated());
        self::assertSame(true, $route->isApiRoute());
        self::assertSame('#^/foo$#uD', $route->getPattern());

        self::assertTrue($route->hasAttribute('id'));
        self::assertFalse($route->hasAttribute('foo'));
        self::assertSame('1', $route->getAttribute('id'));
    }

    public function testWithAddedAttributes(): void
    {
        $route = new Route(name: 'foo', path: '/foo', requestHandler: '@requestHandler');
        self::assertSame([], $route->getAttributes());

        $clone1 = $route->withAddedAttributes(['foo' => 'bar']);
        self::assertNotSame($route, $clone1);
        self::assertSame(['foo' => 'bar'], $clone1->getAttributes());

        $clone2 = $clone1->withAddedAttributes(['bar' => 'baz']);
        self::assertNotSame($clone1, $clone2);
        self::assertSame(['foo' => 'bar', 'bar' => 'baz'], $clone2->getAttributes());
    }
}
