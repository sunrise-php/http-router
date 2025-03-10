<?php

declare(strict_types=1);

namespace Sunrise\Http\Router\Tests\Fixture\App\Dictionary;

enum ProductStatus: int
{
    case Disabled = 0;
    case Enabled = 1;
    case Removed = 2;
    case Archived = 3;
}
