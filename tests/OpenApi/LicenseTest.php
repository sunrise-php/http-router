<?php declare(strict_types=1);

namespace Sunrise\Http\Router\Tests\OpenApi;

/**
 * Import classes
 */
use PHPUnit\Framework\TestCase;
use Sunrise\Http\Router\OpenApi\AbstractObject;
use Sunrise\Http\Router\OpenApi\License;
use Sunrise\Http\Router\OpenApi\ObjectInterface;

/**
 * LicenseTest
 */
class LicenseTest extends TestCase
{

    /**
     * @return void
     */
    public function testContracts() : void
    {
        $object = new License('foo');

        $this->assertInstanceOf(AbstractObject::class, $object);
        $this->assertInstanceOf(ObjectInterface::class, $object);
    }

    /**
     * @return void
     */
    public function testConstructor() : void
    {
        $object = new License('foo');

        $this->assertSame(['name' => 'foo'], $object->toArray());
    }

    /**
     * @return void
     */
    public function testSetUrl() : void
    {
        $object = new License('foo');
        $object->setUrl('bar');

        $this->assertSame([
            'name' => 'foo',
            'url' => 'bar',
        ], $object->toArray());
    }
}
