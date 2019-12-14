<?php declare(strict_types=1);

namespace Sunrise\Http\Router\Tests\OpenApi;

/**
 * Import classes
 */
use PHPUnit\Framework\TestCase;
use Sunrise\Http\Router\OpenApi\ObjectInterface;
use Sunrise\Http\Router\OpenApi\SecurityRequirement;

/**
 * SecurityRequirementTest
 */
class SecurityRequirementTest extends TestCase
{

    /**
     * @return void
     */
    public function testContracts() : void
    {
        $object = new SecurityRequirement('foo');

        $this->assertInstanceOf(ObjectInterface::class, $object);
    }

    /**
     * @return void
     */
    public function testConstructor() : void
    {
        $object = new SecurityRequirement('foo');

        $this->assertSame([
            'foo' => [],
        ], $object->toArray());
    }

    /**
     * @return void
     */
    public function testSetScopes() : void
    {
        $object = new SecurityRequirement('foo');
        $object->setScopes('bar');
        $object->setScopes('baz', 'qux'); // overwrite...

        $this->assertSame([
            'foo' => ['baz', 'qux'],
        ], $object->toArray());
    }
}
