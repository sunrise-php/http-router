<?php declare(strict_types=1);

namespace Sunrise\Http\Router\Tests\Fixture\ControllersDescribedByAttributes;

/**
 * Import classes
 */
use Sunrise\Http\Router\Attribute\Route;
use Sunrise\Http\Router\Tests\Fixture\BlankRequestHandler;

#[Route(name: 'vbyzea', path: '/vbyzea', methods: ['GET'], priority: 6)]
final class vbyzea extends BlankRequestHandler
{
}
