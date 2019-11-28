<?php declare(strict_types=1);

namespace Sunrise\Http\Router\Tests\Loader;

/**
 * Import classes
 */
use PHPUnit\Framework\TestCase;
use Sunrise\Http\Router\Exception\InvalidLoadResourceException;
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
    public function testAttachInvalidResource() : void
    {
        $loader = new CollectableFileLoader();

        $this->expectException(InvalidLoadResourceException::class);
        $this->expectExceptionMessage('The resource "undefined" is not found.');

        $loader->attach('undefined');
    }

    /**
     * @return void
     */
    public function testLoad() : void
    {
        $loader = new CollectableFileLoader();

        $loader->attach(__DIR__ . '/../Fixture/collectable-routes.php');

        $this->assertCount(1, $loader->load()->all());
    }
}
