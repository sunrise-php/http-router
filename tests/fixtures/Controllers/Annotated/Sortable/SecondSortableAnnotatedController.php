<?php declare(strict_types=1);

namespace Sunrise\Http\Router\Test\Fixture\Controllers\Annotated\Sortable;

use Sunrise\Http\Router\Test\Fixture\Controllers\AbstractController;

/**
 * @Route(
 *   name="second-sortable-annotated-controller",
 *   path="/",
 *   method="GET",
 *   priority=2,
 * )
 */
final class SecondSortableAnnotatedController extends AbstractController
{
}
