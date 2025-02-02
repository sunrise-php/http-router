<?php

declare(strict_types=1);

namespace Sunrise\Http\Router\Tests\Fixture\App\View;

final class PageView
{
    public function __construct(
        public readonly string $name,
    ) {
    }
}
