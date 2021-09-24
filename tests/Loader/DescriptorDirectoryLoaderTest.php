<?php declare(strict_types=1);

namespace Sunrise\Http\Router\Tests\Loader;

/**
 * Import classes
 */
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;
use Psr\SimpleCache\CacheInterface;
use Sunrise\Http\Router\Annotation\Route as AnnotationRouteDescriptor;
use Sunrise\Http\Router\Attribute\Route as AttributeRouteDescriptor;
use Sunrise\Http\Router\Exception\InvalidDescriptorArgumentException;
use Sunrise\Http\Router\Exception\InvalidLoaderResourceException;
use Sunrise\Http\Router\Loader\DescriptorDirectoryLoader;
use Sunrise\Http\Router\Loader\LoaderInterface;
use Sunrise\Http\Router\Tests\Fixture;

/**
 * Import constants
 */
use const PHP_MAJOR_VERSION;

/**
 * DescriptorDirectoryLoaderTest
 *
 * @since 2.6.0
 */
class DescriptorDirectoryLoaderTest extends TestCase
{

    /**
     * @return void
     */
    public function testContracts() : void
    {
        $loader = new DescriptorDirectoryLoader();
        $this->assertInstanceOf(LoaderInterface::class, $loader);
    }

    /**
     * @return void
     */
    public function testContainer() : void
    {
        $loader = new DescriptorDirectoryLoader();
        $this->assertNull($loader->getContainer());
        $container = $this->createMock(ContainerInterface::class);
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
        $loader = new DescriptorDirectoryLoader();
        $this->assertNull($loader->getCache());
        $cache = $this->createMock(CacheInterface::class);
        $loader->setCache($cache);
        $this->assertSame($cache, $loader->getCache());
        $loader->setCache(null);
        $this->assertNull($loader->getCache());
    }

    /**
     * @return void
     */
    public function testAttachInvalidResource() : void
    {
        $loader = new DescriptorDirectoryLoader();
        $this->expectException(InvalidLoaderResourceException::class);
        $this->expectExceptionMessage('The resource "undefined" is not found.');
        $loader->attach('undefined');
    }

    /**
     * @return void
     */
    public function testAttachArrayWithInvalidResource() : void
    {
        $loader = new DescriptorDirectoryLoader();
        $this->expectException(InvalidLoaderResourceException::class);
        $this->expectExceptionMessage('The resource "undefined" is not found.');
        $loader->attachArray(['undefined']);
    }

    /**
     * @return void
     *
     * @runInSeparateProcess
     */
    public function testLoadAttributes() : void
    {
        if (8 > PHP_MAJOR_VERSION) {
            $this->markTestSkipped('PHP8 is required...');
            return;
        }

        $loader = new DescriptorDirectoryLoader();
        $loader->attach(__DIR__ . '/../Fixture/ControllersDescribedByAttributes');
        $routes = $loader->load()->all();

        $this->assertCount(10, $routes);

        // sort by priority...
        $this->assertSame([
            'scgeuj',
            'ngbpyq',
            'bjwmqq',
            'vbyzea',
            'mhnyzy',
            'nrwgyq',
            'hmhmkd',
            'zfmamx',
            'ptpqrp',
            'kbncjj',
        ], Fixture\Helper::routesToNames($routes));

        $this->assertContains([
            'name' => 'kbncjj',
            'path' => '/kbncjj',
            'methods' => ['GET'],
            'requestHandler' => Fixture\ControllersDescribedByAttributes\kbncjj::class,
            'middlewares' => [],
            'attributes' => [],
        ], Fixture\Helper::routesToArray($routes));

        $this->assertContains([
            'name' => 'ptpqrp',
            'path' => '/ptpqrp',
            'methods' => ['GET'],
            'requestHandler' => Fixture\ControllersDescribedByAttributes\ptpqrp::class,
            'middlewares' => [],
            'attributes' => [],
        ], Fixture\Helper::routesToArray($routes));

        $this->assertContains([
            'name' => 'zfmamx',
            'path' => '/zfmamx',
            'methods' => ['GET'],
            'requestHandler' => Fixture\ControllersDescribedByAttributes\zfmamx::class,
            'middlewares' => [Fixture\BlankMiddleware::class],
            'attributes' => ['foo' => 'bar'],
        ], Fixture\Helper::routesToArray($routes));

        $this->assertContains([
            'name' => 'hmhmkd',
            'path' => '/hmhmkd',
            'methods' => ['GET'],
            'requestHandler' => Fixture\ControllersDescribedByAttributes\hmhmkd::class,
            'middlewares' => [],
            'attributes' => [],
        ], Fixture\Helper::routesToArray($routes));

        $this->assertContains([
            'name' => 'nrwgyq',
            'path' => '/nrwgyq',
            'methods' => ['GET'],
            'requestHandler' => Fixture\ControllersDescribedByAttributes\nrwgyq::class,
            'middlewares' => [],
            'attributes' => [],
        ], Fixture\Helper::routesToArray($routes));

        $this->assertContains([
            'name' => 'mhnyzy',
            'path' => '/mhnyzy',
            'methods' => ['GET'],
            'requestHandler' => Fixture\ControllersDescribedByAttributes\mhnyzy::class,
            'middlewares' => [],
            'attributes' => [],
        ], Fixture\Helper::routesToArray($routes));

        $this->assertContains([
            'name' => 'vbyzea',
            'path' => '/vbyzea',
            'methods' => ['GET'],
            'requestHandler' => Fixture\ControllersDescribedByAttributes\vbyzea::class,
            'middlewares' => [],
            'attributes' => [],
        ], Fixture\Helper::routesToArray($routes));

        $this->assertContains([
            'name' => 'bjwmqq',
            'path' => '/bjwmqq',
            'methods' => ['GET'],
            'requestHandler' => Fixture\ControllersDescribedByAttributes\bjwmqq::class,
            'middlewares' => [],
            'attributes' => [],
        ], Fixture\Helper::routesToArray($routes));

        $this->assertContains([
            'name' => 'ngbpyq',
            'path' => '/ngbpyq',
            'methods' => ['GET'],
            'requestHandler' => Fixture\ControllersDescribedByAttributes\ngbpyq::class,
            'middlewares' => [],
            'attributes' => [],
        ], Fixture\Helper::routesToArray($routes));

        $this->assertContains([
            'name' => 'scgeuj',
            'path' => '/scgeuj',
            'methods' => ['GET'],
            'requestHandler' => Fixture\ControllersDescribedByAttributes\scgeuj::class,
            'middlewares' => [],
            'attributes' => [],
        ], Fixture\Helper::routesToArray($routes));

        $this->assertContains([
            'summary' => 'foo',
            'description' => 'bar',
            'tags' => ['foo', 'bar'],
        ], Fixture\Helper::routesToArray204($routes));

        $this->assertContains([
            'host' => '127.0.0.1',
        ], Fixture\Helper::routesToArray206($routes));
    }

    /**
     * @return void
     *
     * @runInSeparateProcess
     */
    public function testMultipleLoadAttributes() : void
    {
        if (8 > PHP_MAJOR_VERSION) {
            $this->markTestSkipped('PHP8 is required...');
            return;
        }

        $loader = new DescriptorDirectoryLoader();

        $loader->attach(__DIR__ . '/../Fixture/ControllersDescribedByAttributes/aeydjc/epdtbx');
        $loader->attach(__DIR__ . '/../Fixture/ControllersDescribedByAttributes/aeydjc/tzdzty');

        $loader->attachArray([__DIR__ . '/../Fixture/ControllersDescribedByAttributes/eercds/aqpwce']);
        $loader->attachArray([__DIR__ . '/../Fixture/ControllersDescribedByAttributes/eercds/pmkqjr']);

        $loader->attachArray([
            __DIR__ . '/../Fixture/ControllersDescribedByAttributes/sfrjse/hsenem',
            __DIR__ . '/../Fixture/ControllersDescribedByAttributes/sfrjse/vswsgm',
        ]);

        $routes = $loader->load()->all();

        $this->assertCount(7, $routes);
    }
}
