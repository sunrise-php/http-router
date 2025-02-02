<?php

declare(strict_types=1);

namespace Sunrise\Http\Router\Tests\Fixture\App\Dto;

final class PageCreateRequest
{
    public function __construct(
        public readonly string $name,
    ) {
    }
}
