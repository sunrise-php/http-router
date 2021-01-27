<?php declare(strict_types=1);

namespace Sunrise\Http\Router\Tests\Fixture\ControllersDescribedByAttributes;

/**
 * Import classes
 */
use Sunrise\Http\Router\Attribute\Route;
use Sunrise\Http\Router\Tests\Fixture\BlankRequestHandler;

#[Route(name: 'hmhmkd', path: '/hmhmkd', methods: ['GET'], priority: 3)]
final class hmhmkd extends BlankRequestHandler
{
}
