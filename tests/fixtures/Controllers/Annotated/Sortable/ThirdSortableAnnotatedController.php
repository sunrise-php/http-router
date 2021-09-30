<?php declare(strict_types=1);

namespace Sunrise\Http\Router\Test\Fixture\Controllers\Annotated\Sortable;

use Sunrise\Http\Router\Test\Fixture\Controllers\AbstractController;

/**
 * @Route(
 *   name="third-sortable-annotated-controller",
 *   path="/",
 *   method="GET",
 *   priority=3,
 * )
 */
final class ThirdSortableAnnotatedController extends AbstractController
{
}
