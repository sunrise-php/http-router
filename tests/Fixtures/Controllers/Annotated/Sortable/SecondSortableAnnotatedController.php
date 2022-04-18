<?php declare(strict_types=1);

namespace Sunrise\Http\Router\Tests\Fixtures\Controllers\Annotated\Sortable;

use Sunrise\Http\Router\Tests\Fixtures\Controllers\AbstractController;

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
