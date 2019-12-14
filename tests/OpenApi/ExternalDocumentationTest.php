<?php declare(strict_types=1);

namespace Sunrise\Http\Router\Tests\OpenApi;

/**
 * Import classes
 */
use PHPUnit\Framework\TestCase;
use Sunrise\Http\Router\OpenApi\AbstractObject;
use Sunrise\Http\Router\OpenApi\ExternalDocumentation;
use Sunrise\Http\Router\OpenApi\ObjectInterface;

/**
 * ExternalDocumentationTest
 */
class ExternalDocumentationTest extends TestCase
{

    /**
     * @return void
     */
    public function testContracts() : void
    {
        $object = new ExternalDocumentation('foo');

        $this->assertInstanceOf(AbstractObject::class, $object);
        $this->assertInstanceOf(ObjectInterface::class, $object);
    }

    /**
     * @return void
     */
    public function testConstructor() : void
    {
        $object = new ExternalDocumentation('foo');

        $this->assertSame([
            'url' => 'foo',
        ], $object->toArray());
    }

    /**
     * @return void
     */
    public function testSetDescription() : void
    {
        $object = new ExternalDocumentation('foo');
        $object->setDescription('bar');

        $this->assertSame([
            'url' => 'foo',
            'description' => 'bar',
        ], $object->toArray());
    }
}
