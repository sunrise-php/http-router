<?php declare(strict_types=1);

namespace Sunrise\Http\Router\Tests;

/**
 * Import classes
 */
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\SimpleCache\CacheInterface;
use Sunrise\Http\Router\Router;
use Sunrise\Http\Router\RouterBuilder;

/**
 * RouterBuilderTest
 */
class RouterBuilderTest extends TestCase
{

    /**
     * @return void
     */
    public function testBuild() : void
    {
        $container = $this->createMock(ContainerInterface::class);

        $cache = $this->createMock(CacheInterface::class);
        $cache->__storage = [];

        $cache->method('set')->will($this->returnCallback(function ($key, $value) use ($cache) {
            $cache->__storage[$key] = $value;
        }));

        $cache->method('get')->will($this->returnCallback(function ($key) use ($cache) {
            return $cache->__storage[$key] ?? null;
        }));

        $middlewares = [];
        $middlewares[] = $this->createMock(MiddlewareInterface::class);
        $middlewares[] = $this->createMock(MiddlewareInterface::class);
        $middlewares[] = $this->createMock(MiddlewareInterface::class);

        $hosts = [];
        $hosts['foo'] = ['foo.net'];
        $hosts['bar'] = ['bar.net'];
        $hosts['baz'] = ['baz.net'];

        $builder = (new RouterBuilder)
            ->setContainer($container)
            ->setCache($cache)
            ->setMiddlewares($middlewares)
            ->setHosts($hosts)
            ->useConfigLoader([__DIR__ . '/Fixture/routes'])
            ->useMetadataLoader([__DIR__ . '/Fixture/Annotation/Route/Valid']);

        $router = $builder->build();

        $this->assertSame($middlewares, $router->getMiddlewares());
        $this->assertSame($hosts, $router->getHosts());
    }
}
