<?php declare(strict_types=1);

namespace Sunrise\Http\Router\Tests\Fixture\ControllersDescribedByAttributes;

/**
 * Import classes
 */
use Sunrise\Http\Router\Attribute\Route;
use Sunrise\Http\Router\Tests\Fixture\BlankRequestHandler;

#[Route(name: 'ngbpyq', path: '/ngbpyq', methods: ['GET'], priority: 8)]
final class ngbpyq extends BlankRequestHandler
{
}
