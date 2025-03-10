<?php

declare(strict_types=1);

namespace Sunrise\Http\Router\Tests\Fixture\App\Dto\Product;

use ArrayObject;

/**
 * @extends ArrayObject<array-key, ProductTagDto>
 */
final class ProductTagDtoCollection extends ArrayObject
{
    public function __construct(ProductTagDto ...$tags)
    {
        parent::__construct($tags);
    }
}
