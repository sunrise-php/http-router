<?php

declare(strict_types=1);

namespace Sunrise\Http\Router\Tests\Fixture\App\Dto\Common;

use Sunrise\Http\Router\Tests\Fixture\App\Config\DomainAgreement;
use Symfony\Component\Validator\Constraints as Assert;

final class PaginationDto
{
    public function __construct(
        #[Assert\Range(min: 1)]
        public readonly int $limit = DomainAgreement::DEFAULT_PAGE_SIZE,
        #[Assert\Range(min: 0)]
        public readonly int $offset = 0,
    ) {
    }
}
