<?php declare(strict_types=1);

namespace Sunrise\Http\Router\Tests\Fixture\Annotation\Route\Invalid;

use Sunrise\Http\Router\Tests\Fixture\BlankRequestHandler;

/**
 * @Route(
 *   path="/",
 *   methods={"GET"}
 * )
 */
class NameMissing extends BlankRequestHandler
{
}
