<?php declare(strict_types=1);

namespace Sunrise\Http\Router\Benchs;

/**
 * Import classes
 */
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Sunrise\Http\Router\RequestHandler\CallableRequestHandler;
use Sunrise\Http\Router\Route;
use Sunrise\Http\Router\Router;
use Sunrise\Http\ServerRequest\ServerRequestFactory;

/**
 * Import functions
 */
use function floor;
use function sprintf;

/**
 * @BeforeMethods({"init"})
 */
class RouterMatchBench
{

    /**
     * @var int
     */
    private $maxRoutes = 10000;

    /**
     * @var ServerRequestInterface
     */
    private $testRequest;

    /**
     * @var RequestHandlerInterface
     */
    private $testRequestHandler;

    /**
     * @return void
     */
    public function init() : void
    {
        $uri = sprintf('/route/%d', floor($this->maxRoutes / 2));

        $this->testRequest = (new ServerRequestFactory)->createServerRequest('GET', $uri);

        $this->testRequestHandler = new CallableRequestHandler(function () {
        });
    }

    /**
     * @Iterations(100)
     *
     * @return void
     */
    public function benchRouterFilledViaBaseMethod() : void
    {
        $router = new Router();

        for ($i = 1; $i <= $this->maxRoutes; $i++) {
            $router->addRoutes(new Route("route:{$i}", "/route/{$i}", ['GET'], $this->testRequestHandler));
        }

        $router->match($this->testRequest);
    }

    /**
     * @Iterations(100)
     *
     * @return void
     */
    public function benchRouterFilledViaShortMethod() : void
    {
        $router = new Router();

        for ($i = 1; $i <= $this->maxRoutes; $i++) {
            $router->get("route:{$i}", "/route/{$i}", $this->testRequestHandler);
        }

        $router->match($this->testRequest);
    }
}
