<?php declare(strict_types=1);

namespace Sunrise\Http\Router\Tests\OpenApi;

/**
 * Import classes
 */
use PHPUnit\Framework\TestCase;
use Sunrise\Http\Router\OpenApi\AbstractObject;
use Sunrise\Http\Router\OpenApi\ObjectInterface;
use Sunrise\Http\Router\OpenApi\ServerVariable;

/**
 * ServerVariableTest
 */
class ServerVariableTest extends TestCase
{

    /**
     * @return void
     */
    public function testContracts() : void
    {
        $object = new ServerVariable('foo', 'bar');

        $this->assertInstanceOf(AbstractObject::class, $object);
        $this->assertInstanceOf(ObjectInterface::class, $object);
    }

    /**
     * @return void
     */
    public function testConstructor() : void
    {
        $object = new ServerVariable('foo', 'bar');

        $this->assertSame([
            'default' => 'bar',
        ], $object->toArray());
    }

    /**
     * @return void
     */
    public function testGetName() : void
    {
        $object = new ServerVariable('foo', 'bar');

        $this->assertSame('foo', $object->getName());
    }

    /**
     * @return void
     */
    public function testSetEnum() : void
    {
        $object = new ServerVariable('foo', 'bar');
        $object->setEnum('baz');
        $object->setEnum('qux', 'quux'); // overwrite...

        $this->assertSame([
            'enum' => ['qux', 'quux'],
            'default' => 'bar',
        ], $object->toArray());
    }

    /**
     * @return void
     */
    public function testSetDescription() : void
    {
        $object = new ServerVariable('foo', 'bar');
        $object->setDescription('baz');

        $this->assertSame([
            'default' => 'bar',
            'description' => 'baz',
        ], $object->toArray());
    }
}
