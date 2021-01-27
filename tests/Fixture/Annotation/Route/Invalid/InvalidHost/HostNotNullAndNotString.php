<?php declare(strict_types=1);

namespace Sunrise\Http\Router\Tests\Fixture\Annotation\Route\Invalid;

use Sunrise\Http\Router\Tests\Fixture\BlankRequestHandler;

/**
 * @Route(
 *   name="home",
 *   host=0,
 *   path="/",
 *   methods={"GET"}
 * )
 */
class InvalidHost extends BlankRequestHandler
{
}
