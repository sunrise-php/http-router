<?php declare(strict_types=1);

namespace Sunrise\Http\Router\Tests\Annotation\OpenApi;

/**
 * Import classes
 */
use PHPUnit\Framework\TestCase;
use Sunrise\Http\Router\Annotation\OpenApi\Example;
use Sunrise\Http\Router\Annotation\OpenApi\ExampleInterface;
use Sunrise\Http\Router\OpenApi\AbstractAnnotation;
use Sunrise\Http\Router\OpenApi\ComponentObjectInterface;
use Sunrise\Http\Router\OpenApi\ObjectInterface;

/**
 * Import functions
 */
use function spl_object_hash;

/**
 * ExampleTest
 */
class ExampleTest extends TestCase
{

    /**
     * @return void
     */
    public function testContracts() : void
    {
        $object = new Example();

        $this->assertInstanceOf(AbstractAnnotation::class, $object);
        $this->assertInstanceOf(ExampleInterface::class, $object);
        $this->assertInstanceOf(ComponentObjectInterface::class, $object);
        $this->assertInstanceOf(ObjectInterface::class, $object);
    }

    /**
     * @return void
     *
     * @link https://github.com/OAI/OpenAPI-Specification/blob/master/versions/3.0.2.md#user-content-componentsexamples
     */
    public function testGetComponentName() : void
    {
        $object = new Example();

        $this->assertSame('examples', $object->getComponentName());
    }

    /**
     * @return void
     */
    public function testGetDefaultReferenceName() : void
    {
        $object = new Example();
        $expected = spl_object_hash($object);

        $this->assertSame($expected, $object->getReferenceName());
    }

    /**
     * @return void
     */
    public function testGetCustomReferenceName() : void
    {
        $object = new Example();
        $object->refName = 'foo';

        $this->assertSame('foo', $object->getReferenceName());
    }

    /**
     * @return void
     */
    public function testIgnoreFields() : void
    {
        $object = new Example();
        $object->refName = 'foo';
        $object->foo = 'bar';

        $this->assertSame(['foo' => 'bar'], $object->toArray());
    }

    /**
     * @return void
     */
    public function testFieldAliases() : void
    {
        $object = new Example();
        $object->anyValue = 'foo';

        $this->assertSame(['value' => 'foo'], $object->toArray());
    }
}
