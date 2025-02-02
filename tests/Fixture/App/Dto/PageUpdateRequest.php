<?php

declare(strict_types=1);

namespace Sunrise\Http\Router\Tests\Fixture\App\Dto;

final class PageUpdateRequest
{
    public function __construct(
        public readonly string $name,
    ) {
    }
}
