<?php

declare(strict_types=1);

namespace Sunrise\Http\Router\Tests\Loader;

use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Psr\SimpleCache\CacheInterface;
use Sunrise\Http\Router\Annotation\ApiRoute;
use Sunrise\Http\Router\Annotation\Consumes;
use Sunrise\Http\Router\Annotation\DefaultAttribute;
use Sunrise\Http\Router\Annotation\DeleteApiRoute;
use Sunrise\Http\Router\Annotation\DeleteMethod;
use Sunrise\Http\Router\Annotation\DeleteRoute;
use Sunrise\Http\Router\Annotation\Deprecated;
use Sunrise\Http\Router\Annotation\Description;
use Sunrise\Http\Router\Annotation\GetApiRoute;
use Sunrise\Http\Router\Annotation\GetMethod;
use Sunrise\Http\Router\Annotation\GetRoute;
use Sunrise\Http\Router\Annotation\HeadApiRoute;
use Sunrise\Http\Router\Annotation\HeadMethod;
use Sunrise\Http\Router\Annotation\HeadRoute;
use Sunrise\Http\Router\Annotation\Method;
use Sunrise\Http\Router\Annotation\Middleware;
use Sunrise\Http\Router\Annotation\NamePrefix;
use Sunrise\Http\Router\Annotation\OptionsApiRoute;
use Sunrise\Http\Router\Annotation\OptionsMethod;
use Sunrise\Http\Router\Annotation\OptionsRoute;
use Sunrise\Http\Router\Annotation\PatchApiRoute;
use Sunrise\Http\Router\Annotation\PatchMethod;
use Sunrise\Http\Router\Annotation\PatchRoute;
use Sunrise\Http\Router\Annotation\PathPostfix;
use Sunrise\Http\Router\Annotation\PathPrefix;
use Sunrise\Http\Router\Annotation\Pattern;
use Sunrise\Http\Router\Annotation\PostApiRoute;
use Sunrise\Http\Router\Annotation\PostMethod;
use Sunrise\Http\Router\Annotation\PostRoute;
use Sunrise\Http\Router\Annotation\Priority;
use Sunrise\Http\Router\Annotation\Produces;
use Sunrise\Http\Router\Annotation\PurgeApiRoute;
use Sunrise\Http\Router\Annotation\PurgeMethod;
use Sunrise\Http\Router\Annotation\PurgeRoute;
use Sunrise\Http\Router\Annotation\PutApiRoute;
use Sunrise\Http\Router\Annotation\PutMethod;
use Sunrise\Http\Router\Annotation\PutRoute;
use Sunrise\Http\Router\Annotation\Route;
use Sunrise\Http\Router\Annotation\Summary;
use Sunrise\Http\Router\Annotation\Tag;
use Sunrise\Http\Router\Dictionary\MediaType;
use Sunrise\Http\Router\Helper\RouteCompiler;
use Sunrise\Http\Router\Loader\DescriptorLoader;
use Sunrise\Http\Router\RouteInterface;
use Sunrise\Http\Router\Router;
use Sunrise\Http\Router\Tests\Fixture\App\Controller\Api\PageController;
use Sunrise\Http\Router\Tests\Fixture\App\Controller\HomeController;
use Sunrise\Http\Router\Tests\Mock\CacheMock;

final class DescriptorLoaderTest extends TestCase
{
    public function testLoadFromDir(): void
    {
        $router = new Router([
            new DescriptorLoader([
                __DIR__ . '/../Fixture/App/Controller',
            ]),
        ]);

        $this->assertTrue($router->hasRoute('home'));
        $route = $router->getRoute('home');
        $this->assertSame('/', $route->getPath());
        $this->assertSame(HomeController::class, $route->getRequestHandler());
        $this->assertSame(['GET'], $route->getMethods());
        $expectedRoutePattern = RouteCompiler::compileRoute($route->getPath(), $route->getPatterns());
        $this->assertSame($expectedRoutePattern, $route->getPattern());

        $this->assertTrue($router->hasRoute('api.pages.create'));
        $route = $router->getRoute('api.pages.create');
        $this->assertSame('/api/pages', $route->getPath());
        $this->assertSame([PageController::class, 'create'], $route->getRequestHandler());
        $this->assertSame(['POST'], $route->getMethods());
        $this->assertSame(['Pages'], $route->getTags());
        $this->assertSame('Creates a new page', $route->getSummary());
        $expectedRoutePattern = RouteCompiler::compileRoute($route->getPath(), $route->getPatterns());
        $this->assertSame($expectedRoutePattern, $route->getPattern());

        $this->assertTrue($router->hasRoute('api.pages.update'));
        $route = $router->getRoute('api.pages.update');
        $this->assertSame('/api/pages/{id}', $route->getPath());
        $this->assertSame([PageController::class, 'update'], $route->getRequestHandler());
        $this->assertSame(['PUT'], $route->getMethods());
        $this->assertSame(['Pages'], $route->getTags());
        $this->assertSame('Updates a page by ID', $route->getSummary());
        $expectedRoutePattern = RouteCompiler::compileRoute($route->getPath(), $route->getPatterns());
        $this->assertSame($expectedRoutePattern, $route->getPattern());
    }

    public function testLoadFromFile(): void
    {
        $router = new Router([
            new DescriptorLoader([
                __DIR__ . '/../Fixture/App/Controller/Api/PageController.php',
            ]),
        ]);

        $this->assertTrue($router->hasRoute('api.pages.create'));
        $route = $router->getRoute('api.pages.create');
        $this->assertSame('/api/pages', $route->getPath());
        $this->assertSame([PageController::class, 'create'], $route->getRequestHandler());
        $this->assertSame(['POST'], $route->getMethods());
        $this->assertSame(['Pages'], $route->getTags());
        $this->assertSame('Creates a new page', $route->getSummary());
        $expectedRoutePattern = RouteCompiler::compileRoute($route->getPath(), $route->getPatterns());
        $this->assertSame($expectedRoutePattern, $route->getPattern());

        $this->assertTrue($router->hasRoute('api.pages.update'));
        $route = $router->getRoute('api.pages.update');
        $this->assertSame('/api/pages/{id}', $route->getPath());
        $this->assertSame([PageController::class, 'update'], $route->getRequestHandler());
        $this->assertSame(['PUT'], $route->getMethods());
        $this->assertSame(['Pages'], $route->getTags());
        $this->assertSame('Updates a page by ID', $route->getSummary());
        $expectedRoutePattern = RouteCompiler::compileRoute($route->getPath(), $route->getPatterns());
        $this->assertSame($expectedRoutePattern, $route->getPattern());
    }

    public function testLoadFromClass(): void
    {
        $router = new Router([
            new DescriptorLoader([
                PageController::class,
            ]),
        ]);

        $this->assertTrue($router->hasRoute('api.pages.create'));
        $route = $router->getRoute('api.pages.create');
        $this->assertSame('/api/pages', $route->getPath());
        $this->assertSame([PageController::class, 'create'], $route->getRequestHandler());
        $this->assertSame(['POST'], $route->getMethods());
        $this->assertSame(['Pages'], $route->getTags());
        $this->assertSame('Creates a new page', $route->getSummary());
        $expectedRoutePattern = RouteCompiler::compileRoute($route->getPath(), $route->getPatterns());
        $this->assertSame($expectedRoutePattern, $route->getPattern());

        $this->assertTrue($router->hasRoute('api.pages.update'));
        $route = $router->getRoute('api.pages.update');
        $this->assertSame('/api/pages/{id}', $route->getPath());
        $this->assertSame([PageController::class, 'update'], $route->getRequestHandler());
        $this->assertSame(['PUT'], $route->getMethods());
        $this->assertSame(['Pages'], $route->getTags());
        $this->assertSame('Updates a page by ID', $route->getSummary());
        $expectedRoutePattern = RouteCompiler::compileRoute($route->getPath(), $route->getPatterns());
        $this->assertSame($expectedRoutePattern, $route->getPattern());
    }

    public function testLoadFromCache(): void
    {
        $cache = $this->createMock(CacheInterface::class);
        $loader = new DescriptorLoader([], $cache);
        $cacheKey = DescriptorLoader::DESCRIPTORS_CACHE_KEY;
        $descriptor = new Route('test');
        $cache->expects(self::once())->method('get')->with($cacheKey)->willReturn([$descriptor]);
        $routes = $loader->load();
        $this->assertTrue($routes->valid());
        $route = $routes->current();
        $this->assertInstanceOf(RouteInterface::class, $route);
        $this->assertSame($descriptor->name, $route->getName());
    }

    public function testUpdateCache(): void
    {
        $cache = new CacheMock();
        $loader = new DescriptorLoader([HomeController::class], $cache);
        $routes = $loader->load();
        $this->assertTrue($routes->valid());
        $route = $routes->current();
        $this->assertInstanceOf(RouteInterface::class, $route);
        $cacheKey = DescriptorLoader::DESCRIPTORS_CACHE_KEY;
        /** @var list<Route> $cachedDescriptors */
        $cachedDescriptors = $cache->get($cacheKey);
        $this->assertArrayHasKey(0, $cachedDescriptors);
        $this->assertSame($route->getName(), $cachedDescriptors[0]->name);
    }

    public function testClearCache(): void
    {
        $cache = $this->createMock(CacheInterface::class);
        $loader = new DescriptorLoader([], $cache);
        $cacheKey = DescriptorLoader::DESCRIPTORS_CACHE_KEY;
        $cache->expects(self::once())->method('delete')->with($cacheKey);
        $loader->clearCache();
    }

    public function testInvalidResource(): void
    {
        $loader = new DescriptorLoader(['']);
        $this->expectException(InvalidArgumentException::class);
        $loader->load()->valid();
    }

    public function testPrivateClassMethod(): void
    {
        $controller = new class
        {
            #[Route('test')]
            private function test(): void
            {
            }
        };

        $this->assertFalse((new DescriptorLoader([$controller::class]))->load()->valid());
    }

    public function testProtectedClassMethod(): void
    {
        $controller = new class
        {
            #[Route('test')]
            protected function test(): void
            {
            }
        };

        $this->assertFalse((new DescriptorLoader([$controller::class]))->load()->valid());
    }

    public function testStaticClassMethod(): void
    {
        $controller = new class
        {
            #[Route('test')]
            public static function test(): void
            {
            }
        };

        $this->assertFalse((new DescriptorLoader([$controller::class]))->load()->valid());
    }

    public function testClassRequestHandler(): void
    {
        $controller = new #[Route(self::class)] class ('89c854d6-0e82-47da-b9c8-001b77e2417a') extends TestCase implements RequestHandlerInterface
        {
            public function handle(ServerRequestInterface $request): ResponseInterface
            {
                return $this->createMock(ResponseInterface::class);
            }
        };

        $route = (new DescriptorLoader([$controller::class]))->load()->current();
        $this->assertInstanceOf(RouteInterface::class, $route);
        $this->assertSame($controller::class, $route->getRequestHandler());
    }

    public function testClassMethodRequestHandler(): void
    {
        $controller = new class
        {
            #[Route('test')]
            public function test(): void
            {
            }
        };

        $route = (new DescriptorLoader([$controller::class]))->load()->current();
        $this->assertInstanceOf(RouteInterface::class, $route);
        $this->assertSame([$controller::class, 'test'], $route->getRequestHandler());
    }

    public function testLowercaseMethod(): void
    {
        $controller = new class
        {
            #[Route('test', methods: ['get'])]
            public function test(): void
            {
            }
        };

        $route = (new DescriptorLoader([$controller::class]))->load()->current();
        $this->assertInstanceOf(RouteInterface::class, $route);
        $this->assertSame(['GET'], $route->getMethods());
    }

    public function testPriorityAnnotation(): void
    {
        $controller = new class
        {
            #[Route('foo'), Priority(1)]
            public function foo(): void
            {
            }

            #[Route('bar'), Priority(2)]
            public function bar(): void
            {
            }
        };

        /** @var list<RouteInterface> $routes */
        $routes = [...(new DescriptorLoader([$controller::class]))->load()];
        $this->assertArrayHasKey(0, $routes);
        $this->assertSame('bar', $routes[0]->getName());
        $this->assertArrayHasKey(1, $routes);
        $this->assertSame("foo", $routes[1]->getName());
    }

    public function testNamePrefixAnnotation(): void
    {
        $controller = new #[NamePrefix('foo.')] class
        {
            #[Route('test')]
            public function test(): void
            {
            }
        };

        $route = (new DescriptorLoader([$controller::class]))->load()->current();
        $this->assertInstanceOf(RouteInterface::class, $route);
        $this->assertSame('foo.test', $route->getName());
    }

    public function testPathPrefixAnnotation(): void
    {
        $controller = new #[PathPrefix('/api')] class
        {
            #[Route('test', '/test')]
            public function test(): void
            {
            }
        };

        $route = (new DescriptorLoader([$controller::class]))->load()->current();
        $this->assertInstanceOf(RouteInterface::class, $route);
        $this->assertSame('/api/test', $route->getPath());
    }

    public function testPathPostfixAnnotation(): void
    {
        $controller = new #[PathPostfix('.json')] class
        {
            #[Route('test', '/test')]
            public function test(): void
            {
            }
        };

        $route = (new DescriptorLoader([$controller::class]))->load()->current();
        $this->assertInstanceOf(RouteInterface::class, $route);
        $this->assertSame('/test.json', $route->getPath());
    }

    public function testPatternAnnotation(): void
    {
        $controller = new class
        {
            #[Route('test', '/test/{foo}'), Pattern('foo', 'bar')]
            public function test(): void
            {
            }
        };

        $route = (new DescriptorLoader([$controller::class]))->load()->current();
        $this->assertInstanceOf(RouteInterface::class, $route);
        $this->assertSame(['foo' => 'bar'], $route->getPatterns());
    }

    public function testMethodAnnotation(): void
    {
        $controller = new class
        {
            #[Route('test'), Method('GET')]
            public function test(): void
            {
            }
        };

        $route = (new DescriptorLoader([$controller::class]))->load()->current();
        $this->assertInstanceOf(RouteInterface::class, $route);
        $this->assertSame(['GET'], $route->getMethods());
    }

    public function testDefaultAttributeAnnotation(): void
    {
        $controller = new class
        {
            #[Route('test'), DefaultAttribute('foo', 'bar')]
            public function test(): void
            {
            }
        };

        $route = (new DescriptorLoader([$controller::class]))->load()->current();
        $this->assertInstanceOf(RouteInterface::class, $route);
        $this->assertSame('bar', $route->getAttribute('foo'));
    }

    public function testMiddlewareAnnotation(): void
    {
        $controller = new class
        {
            #[Route('test'), Middleware('foo')]
            public function test(): void
            {
            }
        };

        $route = (new DescriptorLoader([$controller::class]))->load()->current();
        $this->assertInstanceOf(RouteInterface::class, $route);
        $this->assertSame(['foo'], $route->getMiddlewares());
    }

    public function testConsumesAnnotation(): void
    {
        $controller = new class
        {
            #[Route('test'), Consumes(MediaType::JSON)]
            public function test(): void
            {
            }
        };

        $route = (new DescriptorLoader([$controller::class]))->load()->current();
        $this->assertInstanceOf(RouteInterface::class, $route);
        $this->assertSame([MediaType::JSON], $route->getConsumedMediaTypes());
    }

    public function testProducesAnnotation(): void
    {
        $controller = new class
        {
            #[Route('test'), Produces(MediaType::JSON)]
            public function test(): void
            {
            }
        };

        $route = (new DescriptorLoader([$controller::class]))->load()->current();
        $this->assertInstanceOf(RouteInterface::class, $route);
        $this->assertSame([MediaType::JSON], $route->getProducedMediaTypes());
    }

    public function testTagAnnotation(): void
    {
        $controller = new class
        {
            #[Route('test'), Tag('foo')]
            public function test(): void
            {
            }
        };

        $route = (new DescriptorLoader([$controller::class]))->load()->current();
        $this->assertInstanceOf(RouteInterface::class, $route);
        $this->assertSame(['foo'], $route->getTags());
    }

    public function testSummaryAnnotation(): void
    {
        $controller = new class
        {
            #[Route('test'), Summary('foo')]
            public function test(): void
            {
            }
        };

        $route = (new DescriptorLoader([$controller::class]))->load()->current();
        $this->assertInstanceOf(RouteInterface::class, $route);
        $this->assertSame('foo', $route->getSummary());
    }

    public function testDescriptionAnnotation(): void
    {
        $controller = new class
        {
            #[Route('test'), Description('foo')]
            public function test(): void
            {
            }
        };

        $route = (new DescriptorLoader([$controller::class]))->load()->current();
        $this->assertInstanceOf(RouteInterface::class, $route);
        $this->assertSame('foo', $route->getDescription());
    }

    public function testDeprecatedAnnotation(): void
    {
        $controller = new class
        {
            #[Route('test'), Deprecated]
            public function test(): void
            {
            }
        };

        $route = (new DescriptorLoader([$controller::class]))->load()->current();
        $this->assertInstanceOf(RouteInterface::class, $route);
        $this->assertTrue($route->isDeprecated());
    }

    public function testApiRouteAnnotation(): void
    {
        $controller = new class
        {
            #[ApiRoute('test')]
            public function test(): void
            {
            }
        };

        $route = (new DescriptorLoader([$controller::class]))->load()->current();
        $this->assertInstanceOf(RouteInterface::class, $route);
        $this->assertTrue($route->isApiRoute());
    }

    public function testHeadMethodAnnotation(): void
    {
        $controller = new class
        {
            #[Route('test'), HeadMethod]
            public function test(): void
            {
            }
        };

        $route = (new DescriptorLoader([$controller::class]))->load()->current();
        $this->assertInstanceOf(RouteInterface::class, $route);
        $this->assertSame([Method::METHOD_HEAD], $route->getMethods());
    }

    public function testGetMethodAnnotation(): void
    {
        $controller = new class
        {
            #[Route('test'), GetMethod]
            public function test(): void
            {
            }
        };

        $route = (new DescriptorLoader([$controller::class]))->load()->current();
        $this->assertInstanceOf(RouteInterface::class, $route);
        $this->assertSame([Method::METHOD_GET], $route->getMethods());
    }

    public function testPostMethodAnnotation(): void
    {
        $controller = new class
        {
            #[Route('test'), PostMethod]
            public function test(): void
            {
            }
        };

        $route = (new DescriptorLoader([$controller::class]))->load()->current();
        $this->assertInstanceOf(RouteInterface::class, $route);
        $this->assertSame([Method::METHOD_POST], $route->getMethods());
    }

    public function testPutMethodAnnotation(): void
    {
        $controller = new class
        {
            #[Route('test'), PutMethod]
            public function test(): void
            {
            }
        };

        $route = (new DescriptorLoader([$controller::class]))->load()->current();
        $this->assertInstanceOf(RouteInterface::class, $route);
        $this->assertSame([Method::METHOD_PUT], $route->getMethods());
    }

    public function testPatchMethodAnnotation(): void
    {
        $controller = new class
        {
            #[Route('test'), PatchMethod]
            public function test(): void
            {
            }
        };

        $route = (new DescriptorLoader([$controller::class]))->load()->current();
        $this->assertInstanceOf(RouteInterface::class, $route);
        $this->assertSame([Method::METHOD_PATCH], $route->getMethods());
    }

    public function testDeleteMethodAnnotation(): void
    {
        $controller = new class
        {
            #[Route('test'), DeleteMethod]
            public function test(): void
            {
            }
        };

        $route = (new DescriptorLoader([$controller::class]))->load()->current();
        $this->assertInstanceOf(RouteInterface::class, $route);
        $this->assertSame([Method::METHOD_DELETE], $route->getMethods());
    }

    public function testPurgeMethodAnnotation(): void
    {
        $controller = new class
        {
            #[Route('test'), PurgeMethod]
            public function test(): void
            {
            }
        };

        $route = (new DescriptorLoader([$controller::class]))->load()->current();
        $this->assertInstanceOf(RouteInterface::class, $route);
        $this->assertSame([Method::METHOD_PURGE], $route->getMethods());
    }

    public function testOptionsMethodAnnotation(): void
    {
        $controller = new class
        {
            #[Route('test'), OptionsMethod]
            public function test(): void
            {
            }
        };

        $route = (new DescriptorLoader([$controller::class]))->load()->current();
        $this->assertInstanceOf(RouteInterface::class, $route);
        $this->assertSame([Method::METHOD_OPTIONS], $route->getMethods());
    }

    public function testHeadRouteAnnotation(): void
    {
        $controller = new class
        {
            #[HeadRoute('test')]
            public function test(): void
            {
            }
        };

        $route = (new DescriptorLoader([$controller::class]))->load()->current();
        $this->assertInstanceOf(RouteInterface::class, $route);
        $this->assertSame([Method::METHOD_HEAD], $route->getMethods());
    }

    public function testGetRouteAnnotation(): void
    {
        $controller = new class
        {
            #[GetRoute('test')]
            public function test(): void
            {
            }
        };

        $route = (new DescriptorLoader([$controller::class]))->load()->current();
        $this->assertInstanceOf(RouteInterface::class, $route);
        $this->assertSame([Method::METHOD_GET], $route->getMethods());
    }

    public function testPostRouteAnnotation(): void
    {
        $controller = new class
        {
            #[PostRoute('test')]
            public function test(): void
            {
            }
        };

        $route = (new DescriptorLoader([$controller::class]))->load()->current();
        $this->assertInstanceOf(RouteInterface::class, $route);
        $this->assertSame([Method::METHOD_POST], $route->getMethods());
    }

    public function testPutRouteAnnotation(): void
    {
        $controller = new class
        {
            #[PutRoute('test')]
            public function test(): void
            {
            }
        };

        $route = (new DescriptorLoader([$controller::class]))->load()->current();
        $this->assertInstanceOf(RouteInterface::class, $route);
        $this->assertSame([Method::METHOD_PUT], $route->getMethods());
    }

    public function testPatchRouteAnnotation(): void
    {
        $controller = new class
        {
            #[PatchRoute('test')]
            public function test(): void
            {
            }
        };

        $route = (new DescriptorLoader([$controller::class]))->load()->current();
        $this->assertInstanceOf(RouteInterface::class, $route);
        $this->assertSame([Method::METHOD_PATCH], $route->getMethods());
    }

    public function testDeleteRouteAnnotation(): void
    {
        $controller = new class
        {
            #[DeleteRoute('test')]
            public function test(): void
            {
            }
        };

        $route = (new DescriptorLoader([$controller::class]))->load()->current();
        $this->assertInstanceOf(RouteInterface::class, $route);
        $this->assertSame([Method::METHOD_DELETE], $route->getMethods());
    }

    public function testPurgeRouteAnnotation(): void
    {
        $controller = new class
        {
            #[PurgeRoute('test')]
            public function test(): void
            {
            }
        };

        $route = (new DescriptorLoader([$controller::class]))->load()->current();
        $this->assertInstanceOf(RouteInterface::class, $route);
        $this->assertSame([Method::METHOD_PURGE], $route->getMethods());
    }

    public function testOptionsRouteAnnotation(): void
    {
        $controller = new class
        {
            #[OptionsRoute('test')]
            public function test(): void
            {
            }
        };

        $route = (new DescriptorLoader([$controller::class]))->load()->current();
        $this->assertInstanceOf(RouteInterface::class, $route);
        $this->assertSame([Method::METHOD_OPTIONS], $route->getMethods());
    }

    public function testHeadApiRouteAnnotation(): void
    {
        $controller = new class
        {
            #[HeadApiRoute('test')]
            public function test(): void
            {
            }
        };

        $route = (new DescriptorLoader([$controller::class]))->load()->current();
        $this->assertInstanceOf(RouteInterface::class, $route);
        $this->assertSame([Method::METHOD_HEAD], $route->getMethods());
        $this->assertTrue($route->isApiRoute());
    }

    public function testGetApiRouteAnnotation(): void
    {
        $controller = new class
        {
            #[GetApiRoute('test')]
            public function test(): void
            {
            }
        };

        $route = (new DescriptorLoader([$controller::class]))->load()->current();
        $this->assertInstanceOf(RouteInterface::class, $route);
        $this->assertSame([Method::METHOD_GET], $route->getMethods());
        $this->assertTrue($route->isApiRoute());
    }

    public function testPostApiRouteAnnotation(): void
    {
        $controller = new class
        {
            #[PostApiRoute('test')]
            public function test(): void
            {
            }
        };

        $route = (new DescriptorLoader([$controller::class]))->load()->current();
        $this->assertInstanceOf(RouteInterface::class, $route);
        $this->assertSame([Method::METHOD_POST], $route->getMethods());
        $this->assertTrue($route->isApiRoute());
    }

    public function testPutApiRouteAnnotation(): void
    {
        $controller = new class
        {
            #[PutApiRoute('test')]
            public function test(): void
            {
            }
        };

        $route = (new DescriptorLoader([$controller::class]))->load()->current();
        $this->assertInstanceOf(RouteInterface::class, $route);
        $this->assertSame([Method::METHOD_PUT], $route->getMethods());
        $this->assertTrue($route->isApiRoute());
    }

    public function testPatchApiRouteAnnotation(): void
    {
        $controller = new class
        {
            #[PatchApiRoute('test')]
            public function test(): void
            {
            }
        };

        $route = (new DescriptorLoader([$controller::class]))->load()->current();
        $this->assertInstanceOf(RouteInterface::class, $route);
        $this->assertSame([Method::METHOD_PATCH], $route->getMethods());
        $this->assertTrue($route->isApiRoute());
    }

    public function testDeleteApiRouteAnnotation(): void
    {
        $controller = new class
        {
            #[DeleteApiRoute('test')]
            public function test(): void
            {
            }
        };

        $route = (new DescriptorLoader([$controller::class]))->load()->current();
        $this->assertInstanceOf(RouteInterface::class, $route);
        $this->assertSame([Method::METHOD_DELETE], $route->getMethods());
        $this->assertTrue($route->isApiRoute());
    }

    public function testPurgeApiRouteAnnotation(): void
    {
        $controller = new class
        {
            #[PurgeApiRoute('test')]
            public function test(): void
            {
            }
        };

        $route = (new DescriptorLoader([$controller::class]))->load()->current();
        $this->assertInstanceOf(RouteInterface::class, $route);
        $this->assertSame([Method::METHOD_PURGE], $route->getMethods());
        $this->assertTrue($route->isApiRoute());
    }

    public function testOptionsApiRouteAnnotation(): void
    {
        $controller = new class
        {
            #[OptionsApiRoute('test')]
            public function test(): void
            {
            }
        };

        $route = (new DescriptorLoader([$controller::class]))->load()->current();
        $this->assertInstanceOf(RouteInterface::class, $route);
        $this->assertSame([Method::METHOD_OPTIONS], $route->getMethods());
        $this->assertTrue($route->isApiRoute());
    }
}
