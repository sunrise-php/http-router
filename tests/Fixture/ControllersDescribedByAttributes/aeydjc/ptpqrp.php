<?php declare(strict_types=1);

namespace Sunrise\Http\Router\Tests\Fixture\ControllersDescribedByAttributes;

/**
 * Import classes
 */
use Sunrise\Http\Router\Attribute\Route;
use Sunrise\Http\Router\Tests\Fixture\BlankRequestHandler;

#[Route(name: 'ptpqrp', host: null, path: '/ptpqrp', methods: ['GET'], priority: 1)]
final class ptpqrp extends BlankRequestHandler
{
}
