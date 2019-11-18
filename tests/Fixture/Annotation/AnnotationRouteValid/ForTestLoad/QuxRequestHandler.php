<?php declare(strict_types=1);

namespace Sunrise\Http\Router\Tests\Fixture\Annotation\AnnotationRouteValid\ForTestLoad;

/**
 * Import classes
 */
use Sunrise\Http\Router\Tests\Fixture\BlankRequestHandler;

/**
 * @Route(
 *   name="qux",
 *   path="/qux",
 *   methods={"GET", "POST"},
 *   middlewares={
 *     "Sunrise\Http\Router\Tests\Fixture\BlankMiddleware",
 *     "Sunrise\Http\Router\Tests\Fixture\BlankMiddleware"
 *   },
 *   attributes={
 *     "foo": "bar",
 *     "source": "qux"
 *   },
 *   priority=100
 * )
 */
class QuxRequestHandler extends BlankRequestHandler
{
}
