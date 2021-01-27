<?php declare(strict_types=1);

namespace Sunrise\Http\Router\Tests\Fixture\ControllersDescribedByAttributes;

/**
 * Import classes
 */
use Sunrise\Http\Router\Attribute\Route;
use Sunrise\Http\Router\Tests\Fixture\BlankRequestHandler;

#[Route(name: 'mhnyzy', path: '/mhnyzy', methods: ['GET'], priority: 5)]
final class mhnyzy extends BlankRequestHandler
{
}
