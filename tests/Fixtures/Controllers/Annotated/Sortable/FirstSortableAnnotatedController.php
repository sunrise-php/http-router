<?php declare(strict_types=1);

namespace Sunrise\Http\Router\Tests\Fixtures\Controllers\Annotated\Sortable;

use Sunrise\Http\Router\Tests\Fixtures\Controllers\AbstractController;

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
