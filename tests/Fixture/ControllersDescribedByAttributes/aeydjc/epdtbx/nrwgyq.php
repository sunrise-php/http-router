<?php declare(strict_types=1);

namespace Sunrise\Http\Router\Tests\Fixture\ControllersDescribedByAttributes;

/**
 * Import classes
 */
use Sunrise\Http\Router\Attribute\Route;
use Sunrise\Http\Router\Tests\Fixture\BlankRequestHandler;

#[Route(name: 'nrwgyq', path: '/nrwgyq', methods: ['GET'], priority: 4)]
final class nrwgyq extends BlankRequestHandler
{
}
