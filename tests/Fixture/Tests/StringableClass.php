<?php

declare(strict_types=1);

namespace Sunrise\Http\Router\Tests\Fixture\Tests;

use Stringable;

final class StringableClass implements Stringable
{
    public function __construct(
        private readonly string $value,
    ) {
    }

    public function __toString(): string
    {
        return $this->value;
    }
}
