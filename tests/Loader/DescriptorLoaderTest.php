<?php declare(strict_types=1);

namespace Sunrise\Http\Router\Tests\Loader;

/**
 * Import classes
 */
use Doctrine\Common\Annotations\AnnotationRegistry;
use PHPUnit\Framework\TestCase;
use Sunrise\Http\Router\Annotation\Route;
use Sunrise\Http\Router\Exception\InvalidLoaderResourceException;
use Sunrise\Http\Router\Loader\DescriptorLoader;
use Sunrise\Http\Router\Loader\LoaderInterface;
use Sunrise\Http\Router\Tests\Fixtures;
use ReflectionClass;

/**
 * Import functions
 */
use function array_map;
use function class_exists;

/**
 * Import constants
 */
use const PHP_MAJOR_VERSION;

/**
 * DescriptorLoaderTest
 */
class DescriptorLoaderTest extends TestCase
{
    use Fixtures\CacheAwareTrait;
    use Fixtures\ContainerAwareTrait;

    /**
     * {@inheritdoc}
     */
    protected function setUp() : void
    {
        if (class_exists(AnnotationRegistry::class)) {
            /** @scrutinizer ignore-deprecated */ AnnotationRegistry::registerLoader('class_exists');
        }
    }

    /**
     * @return void
     */
    public function testContracts() : void
    {
        $loader = new DescriptorLoader();

        $this->assertInstanceOf(LoaderInterface::class, $loader);
    }

    /**
     * @return void
     */
    public function testContainer() : void
    {
        $container = $this->getContainer();

        $loader = new DescriptorLoader();
        $this->assertNull($loader->getContainer());

        $loader->setContainer($container);
        $this->assertSame($container, $loader->getContainer());

        $loader->setContainer(null);
        $this->assertNull($loader->getContainer());
    }

    /**
     * @return void
     */
    public function testCache() : void
    {
        $cache = $this->getCache();

        $loader = new DescriptorLoader();
        $loader->attach(Fixtures\Controllers\Annotated\CacheableAnnotatedController::class);

        $this->assertNull($loader->getCache());
        $this->assertNull($loader->getCacheKey());

        $loader->setCache($cache);
        $this->assertSame($cache, $loader->getCache());

        $loader->setCacheKey('foo');
        $this->assertSame('foo', $loader->getCacheKey());

        $descriptor = new Route('controller-from-cached-descriptor', null, '/');
        $descriptor->holder = Fixtures\Controllers\BlankController::class;

        $cache->storage[$loader->getCacheKey()][0] = $descriptor;

        $routes = $loader->load();
        $this->assertTrue($routes->has($cache->storage[$loader->getCacheKey()][0]->name));

        $loader->setCache(null);
        $this->assertNull($loader->getCache());

        $loader->setCacheKey(null);
        $this->assertNull($loader->getCacheKey());
    }

    /**
     * @return void
     */
    public function testAttachInvalidResource() : void
    {
        $loader = new DescriptorLoader();

        $this->expectException(InvalidLoaderResourceException::class);
        $this->expectExceptionMessage('The resource "undefined" is not found.');

        $loader->attach('undefined');
    }

    /**
     * @return void
     */
    public function testAttachArrayWithInvalidResource() : void
    {
        $loader = new DescriptorLoader();

        $this->expectException(InvalidLoaderResourceException::class);
        $this->expectExceptionMessage('The resource "undefined" is not found.');

        $loader->attachArray(['undefined']);
    }

    /**
     * @return void
     */
    public function testLoadMinimallyAnnotatedClass() : void
    {
        $class = Fixtures\Controllers\Annotated\MinimallyAnnotatedController::class;

        $loader = new DescriptorLoader();
        $loader->attach($class);

        $routes = $loader->load();
        $this->assertTrue($routes->has('minimally-annotated-controller'));

        $route = $routes->get('minimally-annotated-controller');
        $this->assertSame('minimally-annotated-controller', $route->getName());
        $this->assertSame('/', $route->getPath());
        $this->assertSame(['GET'], $route->getMethods());
    }

    /**
     * @return void
     */
    public function testLoadMinimallyAttributedClass() : void
    {
        if (8 > PHP_MAJOR_VERSION) {
            $this->markTestSkipped('PHP 8 is required...');
            return;
        }

        $class = Fixtures\Controllers\Attributed\MinimallyAttributedController::class;

        $loader = new DescriptorLoader();
        $loader->attach($class);

        $routes = $loader->load();
        $this->assertTrue($routes->has('minimally-attributed-controller'));

        $route = $routes->get('minimally-attributed-controller');
        $this->assertSame('minimally-attributed-controller', $route->getName());
        $this->assertSame('/', $route->getPath());
        $this->assertSame(['GET'], $route->getMethods());
    }

    /**
     * @return void
     */
    public function testLoadMaximallyAnnotatedClass() : void
    {
        $loader = new DescriptorLoader();
        $loader->attach(Fixtures\Controllers\Annotated\MaximallyAnnotatedController::class);

        $routes = $loader->load();
        $this->assertTrue($routes->has('maximally-annotated-controller'));

        $route = $routes->get('maximally-annotated-controller');
        $this->assertSame('maximally-annotated-controller', $route->getName());
        $this->assertSame('local', $route->getHost());
        $this->assertSame('/', $route->getPath());
        $this->assertSame(['HEAD', 'GET'], $route->getMethods());
        $this->assertCount(1, $route->getMiddlewares());
        $this->assertInstanceOf(Fixtures\Middlewares\BlankMiddleware::class, $route->getMiddlewares()[0]);
        $this->assertSame(['foo' => 'bar'], $route->getAttributes());
        $this->assertSame('Lorem ipsum', $route->getSummary());
        $this->assertSame('Lorem ipsum dolor sit amet', $route->getDescription());
        $this->assertSame(['foo', 'bar'], $route->getTags());
    }

    /**
     * @return void
     */
    public function testLoadMaximallyAttributedClass() : void
    {
        if (8 > PHP_MAJOR_VERSION) {
            $this->markTestSkipped('PHP 8 is required...');
            return;
        }

        $loader = new DescriptorLoader();
        $loader->attach(Fixtures\Controllers\Attributed\MaximallyAttributedController::class);

        $routes = $loader->load();
        $this->assertTrue($routes->has('maximally-attributed-controller'));

        $route = $routes->get('maximally-attributed-controller');
        $this->assertSame('maximally-attributed-controller', $route->getName());
        $this->assertSame('local', $route->getHost());
        $this->assertSame('/', $route->getPath());
        $this->assertSame(['HEAD', 'GET'], $route->getMethods());
        $this->assertCount(1, $route->getMiddlewares());
        $this->assertInstanceOf(Fixtures\Middlewares\BlankMiddleware::class, $route->getMiddlewares()[0]);
        $this->assertSame(['foo' => 'bar'], $route->getAttributes());
        $this->assertSame('Lorem ipsum', $route->getSummary());
        $this->assertSame('Lorem ipsum dolor sit amet', $route->getDescription());
        $this->assertSame(['foo', 'bar'], $route->getTags());
    }

    /**
     * @return void
     */
    public function testLoadGroupedAnnotatedClass() : void
    {
        $loader = new DescriptorLoader();
        $loader->attach(Fixtures\Controllers\Annotated\GroupedAnnotatedController::class);

        $routes = $loader->load();
        $this->assertTrue($routes->has('first-from-grouped-annotated-controller'));
        $this->assertTrue($routes->has('second-from-grouped-annotated-controller'));
        $this->assertTrue($routes->has('third-from-grouped-annotated-controller'));

        $route = $routes->get('first-from-grouped-annotated-controller');
        $this->assertSame('host', $route->getHost());
        $this->assertSame('/prefix/first.json', $route->getPath());
        $this->assertSame(['GET'], $route->getMethods());
        $this->assertCount(6, $route->getMiddlewares());

        $route = $routes->get('second-from-grouped-annotated-controller');
        $this->assertSame('host', $route->getHost());
        $this->assertSame('/prefix/second.json', $route->getPath());
        $this->assertSame(['GET'], $route->getMethods());
        $this->assertCount(6, $route->getMiddlewares());

        $route = $routes->get('third-from-grouped-annotated-controller');
        $this->assertSame('host', $route->getHost());
        $this->assertSame('/prefix/third.json', $route->getPath());
        $this->assertSame(['GET'], $route->getMethods());
        $this->assertCount(6, $route->getMiddlewares());

        $this->assertFalse($routes->has('private-from-grouped-annotated-controller'));
        $this->assertFalse($routes->has('protected-from-grouped-annotated-controller'));
        $this->assertFalse($routes->has('static-from-grouped-annotated-controller'));
    }

    /**
     * @return void
     */
    public function testLoadGroupedAttributedClass() : void
    {
        if (8 > PHP_MAJOR_VERSION) {
            $this->markTestSkipped('PHP 8 is required...');
            return;
        }

        $loader = new DescriptorLoader();
        $loader->attach(Fixtures\Controllers\Attributed\GroupedAttributedController::class);

        $routes = $loader->load();
        $this->assertTrue($routes->has('first-from-grouped-attributed-controller'));
        $this->assertTrue($routes->has('second-from-grouped-attributed-controller'));
        $this->assertTrue($routes->has('third-from-grouped-attributed-controller'));

        $route = $routes->get('first-from-grouped-attributed-controller');
        $this->assertSame('host', $route->getHost());
        $this->assertSame('/prefix/first.json', $route->getPath());
        $this->assertSame(['GET'], $route->getMethods());
        $this->assertCount(6, $route->getMiddlewares());

        $route = $routes->get('second-from-grouped-attributed-controller');
        $this->assertSame('host', $route->getHost());
        $this->assertSame('/prefix/second.json', $route->getPath());
        $this->assertSame(['GET'], $route->getMethods());
        $this->assertCount(6, $route->getMiddlewares());

        $route = $routes->get('third-from-grouped-attributed-controller');
        $this->assertSame('host', $route->getHost());
        $this->assertSame('/prefix/third.json', $route->getPath());
        $this->assertSame(['GET'], $route->getMethods());
        $this->assertCount(6, $route->getMiddlewares());
    }

    /**
     * @return void
     */
    public function testLoadSeveralAnnotatedClasses() : void
    {
        $loader = new DescriptorLoader();
        $loader->attachArray([
            Fixtures\Controllers\Annotated\MinimallyAnnotatedController::class,
            Fixtures\Controllers\Annotated\MaximallyAnnotatedController::class,
        ]);

        $routes = $loader->load();
        $this->assertTrue($routes->has('minimally-annotated-controller'));
        $this->assertTrue($routes->has('maximally-annotated-controller'));
    }

    /**
     * @return void
     */
    public function testLoadDirectoryWithAnnotatedClasses() : void
    {
        $loader = new DescriptorLoader();
        $loader->attach(__DIR__ . '/../Fixtures/Controllers/Annotated/Loadable');

        $routes = $loader->load();
        $this->assertTrue($routes->has('first-loadable-annotated-controller'));
        $this->assertTrue($routes->has('second-loadable-annotated-controller'));
    }

    /**
     * @return void
     */
    public function testLoadSortableAnnotatedClasses() : void
    {
        $loader = new DescriptorLoader();
        $loader->attach(Fixtures\Controllers\Annotated\Sortable\FirstSortableAnnotatedController::class);
        $loader->attach(Fixtures\Controllers\Annotated\Sortable\SecondSortableAnnotatedController::class);
        $loader->attach(Fixtures\Controllers\Annotated\Sortable\ThirdSortableAnnotatedController::class);

        $routes = $loader->load();
        $this->assertTrue($routes->has('first-sortable-annotated-controller'));
        $this->assertTrue($routes->has('second-sortable-annotated-controller'));
        $this->assertTrue($routes->has('third-sortable-annotated-controller'));

        $this->assertSame([
            'third-sortable-annotated-controller',
            'second-sortable-annotated-controller',
            'first-sortable-annotated-controller',
        ], array_map(function ($route) {
            return $route->getName();
        }, $routes->all()));
    }

    /**
     * @return void
     */
    public function testLoadAbstractAnnotatedClass() : void
    {
        $loader = new DescriptorLoader();
        $loader->attach(Fixtures\Controllers\Annotated\AbstractAnnotatedController::class);

        $routes = $loader->load();
        $this->assertFalse($routes->has('abstract-annotated-controller'));
    }
}
