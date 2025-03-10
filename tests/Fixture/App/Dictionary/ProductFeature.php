<?php

declare(strict_types=1);

namespace Sunrise\Http\Router\Tests\Fixture\App\Dictionary;

enum ProductFeature: string
{
    case FastDelivery = 'fast-delivery';
    case FreeDelivery = 'free-delivery';
}
