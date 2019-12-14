<?php declare(strict_types=1);

namespace Sunrise\Http\Router\Tests\OpenApi;

/**
 * Import classes
 */
use PHPUnit\Framework\TestCase;
use Sunrise\Http\Router\OpenApi\AbstractObject;
use Sunrise\Http\Router\OpenApi\ExternalDocumentation;
use Sunrise\Http\Router\OpenApi\ObjectInterface;
use Sunrise\Http\Router\OpenApi\Tag;

/**
 * TagTest
 */
class TagTest extends TestCase
{

    /**
     * @return void
     */
    public function testContracts() : void
    {
        $object = new Tag('foo');

        $this->assertInstanceOf(AbstractObject::class, $object);
        $this->assertInstanceOf(ObjectInterface::class, $object);
    }

    /**
     * @return void
     */
    public function testConstructor() : void
    {
        $object = new Tag('foo');

        $this->assertSame([
            'name' => 'foo',
        ], $object->toArray());
    }

    /**
     * @return void
     */
    public function testSetDescription() : void
    {
        $object = new Tag('foo');
        $object->setDescription('bar');

        $this->assertSame([
            'name' => 'foo',
            'description' => 'bar',
        ], $object->toArray());
    }

    /**
     * @return void
     */
    public function testSetExternalDocs() : void
    {
        $object = new Tag('foo');
        $object->setExternalDocs(new ExternalDocumentation('bar'));

        $this->assertSame([
            'name' => 'foo',
            'externalDocs' => [
                'url' => 'bar',
            ],
        ], $object->toArray());
    }
}
