<?php declare(strict_types=1);

namespace Sunrise\Http\Router\Tests\OpenApi;

/**
 * Import classes
 */
use PHPUnit\Framework\TestCase;
use Sunrise\Http\Router\OpenApi\AbstractObject;
use Sunrise\Http\Router\OpenApi\OAuthFlow;
use Sunrise\Http\Router\OpenApi\ObjectInterface;

/**
 * OAuthFlowTest
 */
class OAuthFlowTest extends TestCase
{

    /**
     * @return void
     */
    public function testContracts() : void
    {
        $object = new OAuthFlow();

        $this->assertInstanceOf(AbstractObject::class, $object);
        $this->assertInstanceOf(ObjectInterface::class, $object);
    }

    /**
     * @return void
     */
    public function testSetAuthorizationUrl() : void
    {
        $object = new OAuthFlow();
        $object->setAuthorizationUrl('foo');

        $this->assertSame([
            'authorizationUrl' => 'foo',
        ], $object->toArray());
    }

    /**
     * @return void
     */
    public function testSetTokenUrl() : void
    {
        $object = new OAuthFlow();
        $object->setTokenUrl('foo');

        $this->assertSame([
            'tokenUrl' => 'foo',
        ], $object->toArray());
    }

    /**
     * @return void
     */
    public function testSetRefreshUrl() : void
    {
        $object = new OAuthFlow();
        $object->setRefreshUrl('foo');

        $this->assertSame([
            'refreshUrl' => 'foo',
        ], $object->toArray());
    }

    /**
     * @return void
     */
    public function testAddScope() : void
    {
        $object = new OAuthFlow();
        $object->addScope('foo', 'bar');
        $object->addScope('baz', 'qux');

        $this->assertSame([
            'scopes' => [
                'foo' => 'bar',
                'baz' => 'qux',
            ],
        ], $object->toArray());
    }
}
