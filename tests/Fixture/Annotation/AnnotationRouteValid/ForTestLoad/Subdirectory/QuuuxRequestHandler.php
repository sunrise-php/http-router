<?php declare(strict_types=1);

namespace Sunrise\Http\Router\Tests\Fixture\Annotation\AnnotationRouteValid\ForTestLoad\Subdirectory;

/**
 * Import classes
 */
use Sunrise\Http\Router\Tests\Fixture\BlankRequestHandler;

/**
 * @Route(
 *   name="quuux",
 *   path="/quuux",
 *   methods={"PUT", "PATCH"},
 *   middlewares={
 *     "Sunrise\Http\Router\Tests\Fixture\BlankMiddleware",
 *     "Sunrise\Http\Router\Tests\Fixture\BlankMiddleware"
 *   },
 *   attributes={
 *     "foo": "bar",
 *     "source": "quuux"
 *   },
 *   priority=300
 * )
 */
class QuuuxRequestHandler extends BlankRequestHandler
{
}
