<?php declare(strict_types=1);

namespace Sunrise\Http\Router\Tests;

/**
 * Import classes
 */
use PHPUnit\Framework\TestCase;
use Sunrise\Http\Router\Exception\RouteNotFoundException;
use Sunrise\Http\Router\Router;
use Sunrise\Http\Router\RouterBuilder;

/**
 * RouterBuilderTest
 */
class RouterBuilderTest extends TestCase
{
    use Fixtures\CacheAwareTrait;
    use Fixtures\ContainerAwareTrait;

    /**
     * @return void
     *
     * @runInSeparateProcess
     */
    public function testBuild() : void
    {
        $container = $this->getContainer();
        $cache = $this->getCache();

        $middlewares = [];
        $middlewares[] = new Fixtures\Middlewares\BlankMiddleware();
        $middlewares[] = new Fixtures\Middlewares\BlankMiddleware();
        $middlewares[] = new Fixtures\Middlewares\BlankMiddleware();

        $hosts = [];
        $hosts['foo'] = ['foo.net'];
        $hosts['bar'] = ['bar.net'];
        $hosts['baz'] = ['baz.net'];

        $router = (new RouterBuilder)
            ->setContainer($container)
            ->setCache($cache)
            ->setCacheKey('foo')
            ->useConfigLoader([
                __DIR__ . '/fixtures/routes/foo.php',
                __DIR__ . '/fixtures/routes/bar.php',
            ])
            ->useMetadataLoader([
                Fixtures\Controllers\Annotated\MinimallyAnnotatedController::class,
                Fixtures\Controllers\Annotated\MaximallyAnnotatedController::class,
            ])
            ->setHosts($hosts)
            ->setMiddlewares($middlewares)
            ->build();

        $this->assertInstanceOf(Router::class, $router);
        $this->assertSame($middlewares, $router->getMiddlewares());
        $this->assertSame($hosts, $router->getHosts());

        $router->getRoutes('foo');
        $router->getRoutes('bar');

        $router->getRoutes('minimally-annotated-controller');
        $router->getRoutes('maximally-annotated-controller');
    }
}
