<?php declare(strict_types=1);

namespace Sunrise\Http\Router\Tests\Fixture\Annotation\AnnotationRouteInvalid;

use Sunrise\Http\Router\Tests\Fixture\BlankRequestHandler;

/**
 * @Route(
 *   name="",
 *   path="/",
 *   methods={"GET"}
 * )
 */
class AnnotationRouteNameEmpty extends BlankRequestHandler
{
}
