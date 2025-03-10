<?php

declare(strict_types=1);

namespace Sunrise\Http\Router\Tests\Fixture\App\Dto\Page;

use Symfony\Component\Validator\Constraints as Assert;

final class PageFilterRequest
{
    public function __construct(
        #[Assert\Length(max: 255)]
        public readonly ?string $name = null,
    ) {
    }
}
