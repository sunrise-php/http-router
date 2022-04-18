<?php declare(strict_types=1);

namespace Sunrise\Http\Router\Tests\Loader;

/**
 * Import classes
 */
use PHPUnit\Framework\TestCase;
use Sunrise\Http\Router\Exception\InvalidLoaderResourceException;
use Sunrise\Http\Router\Loader\ConfigLoader;
use Sunrise\Http\Router\Loader\LoaderInterface;
use Sunrise\Http\Router\Tests\Fixtures;

/**
 * ConfigLoaderTest
 */
class ConfigLoaderTest extends TestCase
{
    use Fixtures\ContainerAwareTrait;

    /**
     * @return void
     */
    public function testContracts() : void
    {
        $loader = new ConfigLoader();

        $this->assertInstanceOf(LoaderInterface::class, $loader);
    }

    /**
     * @return void
     */
    public function testContainer() : void
    {
        $container = $this->getContainer();

        $loader = new ConfigLoader();
        $this->assertNull($loader->getContainer());

        $loader->setContainer($container);
        $this->assertSame($container, $loader->getContainer());

        $loader->setContainer(null);
        $this->assertNull($loader->getContainer());
    }

    /**
     * @return void
     */
    public function testAttachInvalidResource() : void
    {
        $loader = new ConfigLoader();

        $this->expectException(InvalidLoaderResourceException::class);
        $this->expectExceptionMessage('The resource "undefined" is not found.');

        $loader->attach('undefined');
    }

    /**
     * @return void
     */
    public function testAttachArrayWithInvalidResource() : void
    {
        $loader = new ConfigLoader();

        $this->expectException(InvalidLoaderResourceException::class);
        $this->expectExceptionMessage('The resource "undefined" is not found.');

        $loader->attachArray(['undefined']);
    }

    /**
     * @return void
     *
     * @runInSeparateProcess
     */
    public function testLoadFile() : void
    {
        $loader = new ConfigLoader();

        $loader->attach(__DIR__ . '/../Fixtures/routes/foo.php');

        $routes = $loader->load();

        $this->assertTrue($routes->has('foo'));
    }

    /**
     * @return void
     *
     * @runInSeparateProcess
     */
    public function testLoadSeveralFiles() : void
    {
        $loader = new ConfigLoader();

        $loader->attachArray([
            __DIR__ . '/../Fixtures/routes/foo.php',
            __DIR__ . '/../Fixtures/routes/bar.php',
        ]);

        $routes = $loader->load();

        $this->assertTrue($routes->has('foo'));
        $this->assertTrue($routes->has('bar'));
    }

    /**
     * @return void
     *
     * @runInSeparateProcess
     */
    public function testLoadDirectory() : void
    {
        $loader = new ConfigLoader();

        $loader->attach(__DIR__ . '/../Fixtures/routes');

        $routes = $loader->load();

        $this->assertTrue($routes->has('foo'));
        $this->assertTrue($routes->has('bar'));
    }
}
