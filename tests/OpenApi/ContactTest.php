<?php declare(strict_types=1);

namespace Sunrise\Http\Router\Tests\OpenApi;

/**
 * Import classes
 */
use PHPUnit\Framework\TestCase;
use Sunrise\Http\Router\OpenApi\AbstractObject;
use Sunrise\Http\Router\OpenApi\Contact;
use Sunrise\Http\Router\OpenApi\ObjectInterface;

/**
 * ContactTest
 */
class ContactTest extends TestCase
{

    /**
     * @return void
     */
    public function testContracts() : void
    {
        $object = new Contact('foo');

        $this->assertInstanceOf(AbstractObject::class, $object);
        $this->assertInstanceOf(ObjectInterface::class, $object);
    }

    /**
     * @return void
     */
    public function testConstructor() : void
    {
        $object = new Contact('foo');

        $this->assertSame(['name' => 'foo'], $object->toArray());
    }

    /**
     * @return void
     */
    public function testSetUrl() : void
    {
        $object = new Contact('foo');
        $object->setUrl('bar');

        $this->assertSame([
            'name' => 'foo',
            'url' => 'bar',
        ], $object->toArray());
    }

    /**
     * @return void
     */
    public function testSetEmail() : void
    {
        $object = new Contact('foo');
        $object->setEmail('bar');

        $this->assertSame([
            'name' => 'foo',
            'email' => 'bar',
        ], $object->toArray());
    }
}
