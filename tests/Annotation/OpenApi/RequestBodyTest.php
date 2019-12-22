<?php declare(strict_types=1);

namespace Sunrise\Http\Router\Tests\Annotation\OpenApi;

/**
 * Import classes
 */
use PHPUnit\Framework\TestCase;
use Sunrise\Http\Router\Annotation\OpenApi\RequestBody;
use Sunrise\Http\Router\Annotation\OpenApi\RequestBodyInterface;
use Sunrise\Http\Router\OpenApi\AbstractAnnotation;
use Sunrise\Http\Router\OpenApi\ComponentObjectInterface;
use Sunrise\Http\Router\OpenApi\ObjectInterface;

/**
 * Import functions
 */
use function spl_object_hash;

/**
 * RequestBodyTest
 */
class RequestBodyTest extends TestCase
{

    /**
     * @return void
     */
    public function testContracts() : void
    {
        $object = new RequestBody();

        $this->assertInstanceOf(AbstractAnnotation::class, $object);
        $this->assertInstanceOf(RequestBodyInterface::class, $object);
        $this->assertInstanceOf(ComponentObjectInterface::class, $object);
        $this->assertInstanceOf(ObjectInterface::class, $object);
    }

    /**
     * @return void
     *
     * @link https://github.com/OAI/OpenAPI-Specification/blob/master/versions/3.0.2.md#user-content-componentsrequestbodies
     */
    public function testGetComponentName() : void
    {
        $object = new RequestBody();

        $this->assertSame('requestBodies', $object->getComponentName());
    }

    /**
     * @return void
     */
    public function testGetDefaultReferenceName() : void
    {
        $object = new RequestBody();
        $expected = spl_object_hash($object);

        $this->assertSame($expected, $object->getReferenceName());
    }

    /**
     * @return void
     */
    public function testGetCustomReferenceName() : void
    {
        $object = new RequestBody();
        $object->refName = 'foo';

        $this->assertSame('foo', $object->getReferenceName());
    }

    /**
     * @return void
     */
    public function testIgnoreFields() : void
    {
        $object = new RequestBody();
        $object->refName = 'foo';
        $object->foo = 'bar';

        $this->assertSame(['foo' => 'bar'], $object->toArray());
    }
}
