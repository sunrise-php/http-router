<?php declare(strict_types=1);

namespace Sunrise\Http\Router\Tests\OpenApi;

/**
 * Import classes
 */
use PHPUnit\Framework\TestCase;
use Sunrise\Http\Router\OpenApi\AbstractObject;
use Sunrise\Http\Router\OpenApi\ObjectInterface;
use Sunrise\Http\Router\OpenApi\Server;
use Sunrise\Http\Router\OpenApi\ServerVariable;

/**
 * ServerTest
 */
class ServerTest extends TestCase
{

    /**
     * @return void
     */
    public function testContracts() : void
    {
        $object = new Server('foo');

        $this->assertInstanceOf(AbstractObject::class, $object);
        $this->assertInstanceOf(ObjectInterface::class, $object);
    }

    /**
     * @return void
     */
    public function testConstructor() : void
    {
        $object = new Server('foo');

        $this->assertSame([
            'url' => 'foo',
        ], $object->toArray());
    }

    /**
     * @return void
     */
    public function testSetDescription() : void
    {
        $object = new Server('foo');
        $object->setDescription('bar');

        $this->assertSame([
            'url' => 'foo',
            'description' => 'bar',
        ], $object->toArray());
    }

    /**
     * @return void
     */
    public function testAddVariable() : void
    {
        $object = new Server('foo');
        $object->addVariable(
            new ServerVariable('bar', 'baz'),
            new ServerVariable('qux', 'quux')
        );

        $this->assertSame([
            'url' => 'foo',
            'variables' => [
                'bar' => [
                    'default' => 'baz',
                ],
                'qux' => [
                    'default' => 'quux',
                ],
            ],
        ], $object->toArray());
    }
}
