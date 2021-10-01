<?php declare(strict_types=1);

namespace Sunrise\Http\Router\Test\Fixture\Controllers\Annotated\Sortable;

use Sunrise\Http\Router\Test\Fixture\Controllers\AbstractController;

/**
 * @Route(
 *   name="first-sortable-annotated-controller",
 *   path="/",
 *   method="GET",
 *   priority=1,
 * )
 */
final class FirstSortableAnnotatedController extends AbstractController
{
}
