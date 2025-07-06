<?php

declare(strict_types=1);

namespace Sunrise\Http\Router\Tests\Fixture\App\View;

use Sunrise\Http\Router\OpenApi\Annotation\SchemaName;

#[SchemaName('Page')]
final class PageView
{
    public function __construct(
        public readonly string $name,
    ) {
    }
}
