<?php declare(strict_types=1);

namespace Sunrise\Http\Router\Tests\Fixture\Annotation\Route\Valid;

/**
 * Import classes
 */
use Sunrise\Http\Router\Tests\Fixture\BlankRequestHandler;

/**
 * @Route(
 *   name="home",
 *   host="localhost",
 *   path="/",
 *   methods={"HEAD", "GET"},
 *   middlewares={
 *     "Sunrise\Http\Router\Tests\Fixture\BlankMiddleware",
 *     "Sunrise\Http\Router\Tests\Fixture\BlankMiddleware"
 *   },
 *   attributes={
 *     "foo": "bar",
 *     "bar": "baz"
 *   },
 *   summary="the route summary",
 *   description="the route description",
 *   tags={"foo", "bar"},
 *   priority=1000
 * )
 */
class HomeRequestHandler extends BlankRequestHandler
{
}
