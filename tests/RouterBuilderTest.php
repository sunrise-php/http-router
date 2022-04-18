<?php declare(strict_types=1);

namespace Sunrise\Http\Router\Tests;

/**
 * Import classes
 */
use PHPUnit\Framework\TestCase;
use Sunrise\Http\Router\Exception\RouteNotFoundException;
use Sunrise\Http\Router\Router;
use Sunrise\Http\Router\RouterBuilder;
use Symfony\Component\EventDispatcher\EventDispatcher;

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
        $eventDispatcher = new EventDispatcher();
        $container = $this->getContainer();
        $cache = $this->getCache();

        $middlewares = [];
        $middlewares[] = new Fixtures\Middlewares\BlankMiddleware();
        $middlewares[] = new Fixtures\Middlewares\BlankMiddleware();
        $middlewares[] = new Fixtures\Middlewares\BlankMiddleware();

        $patterns = [];
        $patterns['foo'] = 'bar';
        $patterns['bar'] = 'baz';

        $hosts = [];
        $hosts['foo'] = ['foo.net'];
        $hosts['bar'] = ['bar.net'];
        $hosts['baz'] = ['baz.net'];

        $builder = (new RouterBuilder)
            ->setEventDispatcher($eventDispatcher)
            ->setContainer($container)
            ->setCache($cache)
            ->setCacheKey('foo')
            ->useConfigLoader([
                __DIR__ . '/Fixtures/routes/foo.php',
                __DIR__ . '/Fixtures/routes/bar.php',
            ])
            ->useMetadataLoader([
                Fixtures\Controllers\Annotated\MinimallyAnnotatedController::class,
                Fixtures\Controllers\Annotated\MaximallyAnnotatedController::class,
            ])
            ->setPatterns($patterns)
            ->setHosts($hosts)
            ->setMiddlewares($middlewares);

        Router::$patterns = [];

        $router = $builder->build();

        $this->assertInstanceOf(Router::class, $router);
        $this->assertSame($patterns, Router::$patterns);
        $this->assertSame($hosts, $router->getHosts());
        $this->assertSame($middlewares, $router->getMiddlewares());
        $this->assertSame($eventDispatcher, $router->getEventDispatcher());
        $this->assertTrue($router->hasRoute('foo'));
        $this->assertTrue($router->hasRoute('bar'));
        $this->assertTrue($router->hasRoute('minimally-annotated-controller'));
        $this->assertTrue($router->hasRoute('maximally-annotated-controller'));
    }
}
