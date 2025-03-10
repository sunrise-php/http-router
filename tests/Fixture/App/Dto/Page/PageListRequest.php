<?php

declare(strict_types=1);

namespace Sunrise\Http\Router\Tests\Fixture\App\Dto\Page;

use Sunrise\Http\Router\Tests\Fixture\App\Dto\Common\PaginationDto;

final class PageListRequest
{
    public function __construct(
        public readonly PageFilterRequest $filter = new PageFilterRequest(),
        public readonly PaginationDto $pagination = new PaginationDto(),
    ) {
    }
}
