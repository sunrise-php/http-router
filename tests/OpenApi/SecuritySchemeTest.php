<?php declare(strict_types=1);

namespace Sunrise\Http\Router\Tests\OpenApi;

/**
 * Import classes
 */
use PHPUnit\Framework\TestCase;
use Sunrise\Http\Router\OpenApi\AbstractObject;
use Sunrise\Http\Router\OpenApi\ComponentObjectInterface;
use Sunrise\Http\Router\OpenApi\OAuthFlows;
use Sunrise\Http\Router\OpenApi\ObjectInterface;
use Sunrise\Http\Router\OpenApi\SecurityScheme;

/**
 * SecuritySchemeTest
 */
class SecuritySchemeTest extends TestCase
{

    /**
     * @return void
     */
    public function testContracts() : void
    {
        $object = new SecurityScheme('foo', 'bar');

        $this->assertInstanceOf(AbstractObject::class, $object);
        $this->assertInstanceOf(ComponentObjectInterface::class, $object);
        $this->assertInstanceOf(ObjectInterface::class, $object);
    }

    /**
     * @return void
     */
    public function testConstructor() : void
    {
        $object = new SecurityScheme('foo', 'bar');

        $this->assertSame([
            'type' => 'bar',
        ], $object->toArray());
    }

    /**
     * @return void
     *
     * @link https://github.com/OAI/OpenAPI-Specification/blob/master/versions/3.0.2.md#user-content-componentssecurityschemes
     */
    public function testGetComponentName() : void
    {
        $object = new SecurityScheme('foo', 'bar');

        $this->assertSame('securitySchemes', $object->getComponentName());
    }

    /**
     * @return void
     */
    public function testGetReferenceName() : void
    {
        $object = new SecurityScheme('foo', 'bar');

        $this->assertSame('foo', $object->getReferenceName());
    }

    /**
     * @return void
     */
    public function testSetDescription() : void
    {
        $object = new SecurityScheme('foo', 'bar');
        $object->setDescription('baz');

        $this->assertSame([
            'type' => 'bar',
            'description' => 'baz',
        ], $object->toArray());
    }

    /**
     * @return void
     */
    public function testSetName() : void
    {
        $object = new SecurityScheme('foo', 'bar');
        $object->setName('baz');

        $this->assertSame([
            'type' => 'bar',
            'name' => 'baz',
        ], $object->toArray());
    }

    /**
     * @return void
     */
    public function testSetIn() : void
    {
        $object = new SecurityScheme('foo', 'bar');
        $object->setIn('baz');

        $this->assertSame([
            'type' => 'bar',
            'in' => 'baz',
        ], $object->toArray());
    }

    /**
     * @return void
     */
    public function testSetScheme() : void
    {
        $object = new SecurityScheme('foo', 'bar');
        $object->setScheme('baz');

        $this->assertSame([
            'type' => 'bar',
            'scheme' => 'baz',
        ], $object->toArray());
    }

    /**
     * @return void
     */
    public function testSetBearerFormat() : void
    {
        $object = new SecurityScheme('foo', 'bar');
        $object->setBearerFormat('baz');

        $this->assertSame([
            'type' => 'bar',
            'bearerFormat' => 'baz',
        ], $object->toArray());
    }

    /**
     * @return void
     */
    public function testSetFlows() : void
    {
        $object = new SecurityScheme('foo', 'bar');
        $object->setFlows(new OAuthFlows());

        $this->assertSame([
            'type' => 'bar',
            'flows' => [],
        ], $object->toArray());
    }

    /**
     * @return void
     */
    public function testSetOpenIdConnectUrl() : void
    {
        $object = new SecurityScheme('foo', 'bar');
        $object->setOpenIdConnectUrl('baz');

        $this->assertSame([
            'type' => 'bar',
            'openIdConnectUrl' => 'baz',
        ], $object->toArray());
    }
}
