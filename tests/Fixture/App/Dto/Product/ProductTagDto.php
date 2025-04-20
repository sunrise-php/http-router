<?php

declare(strict_types=1);

namespace Sunrise\Http\Router\Tests\Fixture\App\Dto\Product;

use Symfony\Component\Uid\Uuid;

final class ProductTagDto
{
    public function __construct(
        public readonly Uuid $id,
    ) {
    }
}
