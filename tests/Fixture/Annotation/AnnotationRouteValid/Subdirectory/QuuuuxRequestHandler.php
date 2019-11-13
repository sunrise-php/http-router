<?php declare(strict_types=1);

namespace Sunrise\Http\Router\Tests\Fixture\Annotation\AnnotationRouteValid\Subdirectory;

/**
 * Import classes
 */
use Sunrise\Http\Router\Tests\Fixture\BlankRequestHandler;

/**
 * @Route(
 *   name="quuuux",
 *   path="/quuuux",
 *   methods={"PATCH", "DELETE"},
 *   middlewares={
 *     "Sunrise\Http\Router\Tests\Fixture\BlankMiddleware",
 *     "Sunrise\Http\Router\Tests\Fixture\BlankMiddleware"
 *   },
 *   attributes={
 *     "foo": "bar",
 *     "source": "quuuux"
 *   },
 *   priority=400
 * )
 */
class QuuuuxRequestHandler extends BlankRequestHandler
{
}
