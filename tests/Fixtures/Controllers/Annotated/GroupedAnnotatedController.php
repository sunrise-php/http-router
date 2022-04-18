<?php declare(strict_types=1);

namespace Sunrise\Http\Router\Tests\Fixtures\Controllers\Annotated;

use Sunrise\Http\Router\Tests\Fixtures\Controllers\AbstractController;

/**
 * @Host("host")
 * @Prefix("/prefix")
 * @Postfix(".json")
 * @Middleware("Sunrise\Http\Router\Tests\Fixtures\Middlewares\BlankMiddleware")
 * @Middleware("Sunrise\Http\Router\Tests\Fixtures\Middlewares\BlankMiddleware")
 * @Middleware("Sunrise\Http\Router\Tests\Fixtures\Middlewares\BlankMiddleware")
 */
final class GroupedAnnotatedController extends AbstractController
{

    /**
     * @Route("first-from-grouped-annotated-controller", path="/first")
     * @Middleware("Sunrise\Http\Router\Tests\Fixtures\Middlewares\BlankMiddleware")
     * @Middleware("Sunrise\Http\Router\Tests\Fixtures\Middlewares\BlankMiddleware")
     * @Middleware("Sunrise\Http\Router\Tests\Fixtures\Middlewares\BlankMiddleware")
     */
    public function first($request)
    {
        return $this->handle($request);
    }

    /**
     * @Route("second-from-grouped-annotated-controller", path="/second")
     * @Middleware("Sunrise\Http\Router\Tests\Fixtures\Middlewares\BlankMiddleware")
     * @Middleware("Sunrise\Http\Router\Tests\Fixtures\Middlewares\BlankMiddleware")
     * @Middleware("Sunrise\Http\Router\Tests\Fixtures\Middlewares\BlankMiddleware")
     */
    public function second($request)
    {
        return $this->handle($request);
    }

    /**
     * @Route("third-from-grouped-annotated-controller", path="/third")
     * @Middleware("Sunrise\Http\Router\Tests\Fixtures\Middlewares\BlankMiddleware")
     * @Middleware("Sunrise\Http\Router\Tests\Fixtures\Middlewares\BlankMiddleware")
     * @Middleware("Sunrise\Http\Router\Tests\Fixtures\Middlewares\BlankMiddleware")
     */
    public function third($request)
    {
        return $this->handle($request);
    }

    /**
     * @Route("private-from-grouped-annotated-controller", path="/")
     */
    private function privateAction()
    {
    }

    /**
     * @Route("protected-from-grouped-annotated-controller", path="/")
     */
    protected function protectedAction()
    {
    }

    /**
     * @Route("static-from-grouped-annotated-controller", path="/")
     */
    public static function staticAction()
    {
    }

    public function shouldBeIgnored()
    {
    }
}
