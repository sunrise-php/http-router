<?php declare(strict_types=1);

namespace Sunrise\Http\Router\Tests\OpenApi;

/**
 * Import classes
 */
use PHPUnit\Framework\TestCase;
use Sunrise\Http\Router\OpenApi\AbstractObject;
use Sunrise\Http\Router\OpenApi\OAuthFlow;
use Sunrise\Http\Router\OpenApi\OAuthFlows;
use Sunrise\Http\Router\OpenApi\ObjectInterface;

/**
 * OAuthFlowsTest
 */
class OAuthFlowsTest extends TestCase
{

    /**
     * @return void
     */
    public function testContracts() : void
    {
        $object = new OAuthFlows();

        $this->assertInstanceOf(AbstractObject::class, $object);
        $this->assertInstanceOf(ObjectInterface::class, $object);
    }

    /**
     * @return void
     */
    public function testSetImplicit() : void
    {
        $object = new OAuthFlows();
        $object->setImplicit(new OAuthFlow());

        $this->assertSame([
            'implicit' => [],
        ], $object->toArray());
    }

    /**
     * @return void
     */
    public function testSetPassword() : void
    {
        $object = new OAuthFlows();
        $object->setPassword(new OAuthFlow());

        $this->assertSame([
            'password' => [],
        ], $object->toArray());
    }

    /**
     * @return void
     */
    public function testSetClientCredentials() : void
    {
        $object = new OAuthFlows();
        $object->setClientCredentials(new OAuthFlow());

        $this->assertSame([
            'clientCredentials' => [],
        ], $object->toArray());
    }

    /**
     * @return void
     */
    public function testSetAuthorizationCode() : void
    {
        $object = new OAuthFlows();
        $object->setAuthorizationCode(new OAuthFlow());

        $this->assertSame([
            'authorizationCode' => [],
        ], $object->toArray());
    }
}
