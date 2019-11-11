<?php declare(strict_types=1);

namespace Sunrise\Http\Router\Tests\RequestHandler;

/**
 * Import classes
 */
use PHPUnit\Framework\TestCase;
use Psr\Http\Server\RequestHandlerInterface;
use Sunrise\Http\Router\RequestHandler\RoutableRequestHandler;
use Sunrise\Http\Router\Tests\Fixture;
use Sunrise\Http\ServerRequest\ServerRequestFactory;

/**
 * RoutableRequestHandlerTest
 */
class RoutableRequestHandlerTest extends TestCase
{

    /**
     * @return void
     */
    public function testConstructor() : void
    {
        $testRoute = new Fixture\TestRoute();
        $requestHandler = new RoutableRequestHandler($testRoute);

        $this->assertInstanceOf(RequestHandlerInterface::class, $requestHandler);
    }

    /**
     * @return void
     */
    public function testHandle() : void
    {
        $testRoute = new Fixture\TestRoute();
        $requestHandler = new RoutableRequestHandler($testRoute);

        $request = (new ServerRequestFactory)->createServerRequest('GET', '/');
        $requestHandler->handle($request);

        $this->assertTrue($testRoute->getMiddlewares()[0]->isRunned());
        $this->assertTrue($testRoute->getMiddlewares()[1]->isRunned());
        $this->assertTrue($testRoute->getMiddlewares()[2]->isRunned());
        $this->assertTrue($testRoute->getRequestHandler()->isRunned());

        $expectedAttributes = [RoutableRequestHandler::ATTR_NAME_FOR_ROUTE_NAME => $testRoute->getName()];
        $expectedAttributes += $testRoute->getAttributes();

        $this->assertSame($expectedAttributes, $testRoute->getRequestHandler()->getAttributes());
    }

    /**
     * @return void
     */
    public function testHandleWithBrokenMiddleware() : void
    {
        $testRoute = new Fixture\TestRoute(Fixture\TestRoute::WITH_BROKEN_MIDDLEWARE);
        $requestHandler = new RoutableRequestHandler($testRoute);

        $request = (new ServerRequestFactory)->createServerRequest('GET', '/');
        $requestHandler->handle($request);

        $this->assertTrue($testRoute->getMiddlewares()[0]->isRunned());
        $this->assertTrue($testRoute->getMiddlewares()[1]->isRunned());
        $this->assertFalse($testRoute->getMiddlewares()[2]->isRunned());
        $this->assertFalse($testRoute->getRequestHandler()->isRunned());
        $this->assertSame([], $testRoute->getRequestHandler()->getAttributes());
    }
}
