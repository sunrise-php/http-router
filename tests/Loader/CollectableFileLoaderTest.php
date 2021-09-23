<?php declare(strict_types=1);

namespace Sunrise\Http\Router\Tests\Loader;

/**
 * Import classes
 */
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;
use Sunrise\Http\Router\Exception\InvalidLoaderResourceException;
use Sunrise\Http\Router\Loader\CollectableFileLoader;
use Sunrise\Http\Router\Loader\LoaderInterface;

/**
 * CollectableFileLoaderTest
 */
class CollectableFileLoaderTest extends TestCase
{

    /**
     * @return void
     */
    public function testConstructor() : void
    {
        $loader = new CollectableFileLoader();

        $this->assertInstanceOf(LoaderInterface::class, $loader);
    }

    /**
     * @return void
     */
    public function testContainer() : void
    {
        $loader = new CollectableFileLoader();

        $this->assertNull($loader->getContainer());

        $container = $this->createMock(ContainerInterface::class);

        $loader->setContainer($container);

        $this->assertSame($container, $loader->getContainer());
    }

    /**
     * @return void
     */
    public function testAttachInvalidResource() : void
    {
        $loader = new CollectableFileLoader();

        $this->expectException(InvalidLoaderResourceException::class);
        $this->expectExceptionMessage('The resource "undefined" is not found.');

        $loader->attach('undefined');
    }

    /**
     * @return void
     *
     * @runInSeparateProcess
     */
    public function testAttachArray() : void
    {
        $loader = new CollectableFileLoader();

        $loader->attachArray([
            __DIR__ . '/../Fixture/routes/foo.php',
            __DIR__ . '/../Fixture/routes/bar.php',
        ]);

        $this->assertCount(2, $loader->load()->all());
    }

    /**
     * @return void
     *
     * @runInSeparateProcess
     */
    public function testLoadFile() : void
    {
        $loader = new CollectableFileLoader();

        $loader->attach(__DIR__ . '/../Fixture/routes/foo.php');

        $this->assertCount(1, $loader->load()->all());
    }

    /**
     * @return void
     *
     * @runInSeparateProcess
     */
    public function testLoadDirectory() : void
    {
        $loader = new CollectableFileLoader();

        $loader->attach(__DIR__ . '/../Fixture/routes');

        $this->assertCount(2, $loader->load()->all());
    }
}
