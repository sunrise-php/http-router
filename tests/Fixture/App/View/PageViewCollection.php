<?php

declare(strict_types=1);

namespace Sunrise\Http\Router\Tests\Fixture\App\View;

use ArrayObject;

/**
 * @extends ArrayObject<array-key, PageView>
 */
final class PageViewCollection extends ArrayObject
{
    public function __construct(PageView ...$pages)
    {
        parent::__construct($pages);
    }
}
