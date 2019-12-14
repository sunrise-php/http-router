<?php declare(strict_types=1);

namespace Sunrise\Http\Router\Tests\OpenApi;

/**
 * Import classes
 */
use PHPUnit\Framework\TestCase;
use Sunrise\Http\Router\OpenApi\AbstractObject;
use Sunrise\Http\Router\OpenApi\ComponentObjectInterface;
use Sunrise\Http\Router\OpenApi\ExternalDocumentation;
use Sunrise\Http\Router\OpenApi\Info;
use Sunrise\Http\Router\OpenApi\OpenApi;
use Sunrise\Http\Router\OpenApi\ObjectInterface;
use Sunrise\Http\Router\OpenApi\SecurityRequirement;
use Sunrise\Http\Router\OpenApi\Server;
use Sunrise\Http\Router\OpenApi\Tag;

/**
 * OpenApiTest
 */
class OpenApiTest extends TestCase
{

    /**
     * @return void
     */
    public function testContracts() : void
    {
        $object = new OpenApi(new Info('foo', 'bar'));

        $this->assertInstanceOf(AbstractObject::class, $object);
        $this->assertInstanceOf(ObjectInterface::class, $object);
    }

    /**
     * @return void
     */
    public function testConstructor() : void
    {
        $object = new OpenApi(new Info('foo', 'bar'));

        $this->assertSame([
            'openapi' => '3.0.2',
            'info' => [
                'title' => 'foo',
                'version' => 'bar',
            ],
        ], $object->toArray());
    }

    /**
     * @return void
     */
    public function testAddServer() : void
    {
        $object = new OpenApi(new Info('foo', 'bar'));

        $object->addServer(
            new Server('baz'),
            new Server('qux')
        );

        $this->assertSame([
            'openapi' => '3.0.2',
            'info' => [
                'title' => 'foo',
                'version' => 'bar',
            ],
            'servers' => [
                [
                    'url' => 'baz',
                ],
                [
                    'url' => 'qux',
                ],
            ],
        ], $object->toArray());
    }

    /**
     * @return void
     */
    public function testAddComponentObject() : void
    {
        $object = new OpenApi(new Info('foo', 'bar'));

        $co1 = $this->createMock(ComponentObjectInterface::class);
        $co1->method('getComponentName')->willReturn('foo');
        $co1->method('getReferenceName')->willReturn('bar');
        $co1->method('toArray')->willReturn(['baz']);

        $co2 = $this->createMock(ComponentObjectInterface::class);
        $co2->method('getComponentName')->willReturn('qux');
        $co2->method('getReferenceName')->willReturn('quux');
        $co2->method('toArray')->willReturn(['quuux']);

        $object->addComponentObject($co1, $co2);

        $this->assertSame([
            'openapi' => '3.0.2',
            'info' => [
                'title' => 'foo',
                'version' => 'bar',
            ],
            'components' => [
                'foo' => [
                    'bar' => [
                        'baz',
                    ],
                ],
                'qux' => [
                    'quux' => [
                        'quuux',
                    ],
                ],
            ],
        ], $object->toArray());
    }

    /**
     * @return void
     */
    public function testAddSecurityRequirement() : void
    {
        $object = new OpenApi(new Info('foo', 'bar'));

        $object->addSecurityRequirement(
            new SecurityRequirement('baz'),
            new SecurityRequirement('qux')
        );

        $this->assertSame([
            'openapi' => '3.0.2',
            'info' => [
                'title' => 'foo',
                'version' => 'bar',
            ],
            'security' => [
                [
                    'baz' => [],
                ],
                [
                    'qux' => [],
                ],
            ],
        ], $object->toArray());
    }

    /**
     * @return void
     */
    public function testAddTag() : void
    {
        $object = new OpenApi(new Info('foo', 'bar'));

        $object->addTag(
            new Tag('baz'),
            new Tag('qux')
        );

        $this->assertSame([
            'openapi' => '3.0.2',
            'info' => [
                'title' => 'foo',
                'version' => 'bar',
            ],
            'tags' => [
                [
                    'name' => 'baz',
                ],
                [
                    'name' => 'qux',
                ],
            ],
        ], $object->toArray());
    }

    /**
     * @return void
     */
    public function testSetExternalDocs() : void
    {
        $object = new OpenApi(new Info('foo', 'bar'));

        $object->setExternalDocs(new ExternalDocumentation('baz'));

        $this->assertSame([
            'openapi' => '3.0.2',
            'info' => [
                'title' => 'foo',
                'version' => 'bar',
            ],
            'externalDocs' => [
                'url' => 'baz',
            ],
        ], $object->toArray());
    }
}
