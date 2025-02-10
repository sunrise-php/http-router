<?php

declare(strict_types=1);

namespace Sunrise\Http\Router\Tests\Fixture\Tests;

use Stringable;

final class StringableClass implements Stringable
{
    public function __construct(
        private readonly string $string,
    ) {
    }

    public function __toString(): string
    {
        return $this->string;
    }
}
