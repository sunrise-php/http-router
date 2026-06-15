<?php

declare(strict_types=1);

namespace Sunrise\Http\Router\Tests\Fixture\Tests;

use DateTimeImmutable;
use Sunrise\Hydrator\Annotation\Subtype;

final class PhpDocArrayFixture
{
    /** @var PhpDocArrayItemFixture[] */
    public array $items;

    /** @var list<PhpDocArrayItemFixture> */
    public array $listItems;

    /** @var array<string, PhpDocArrayItemFixture|null> */
    public array $nullableItems;

    /** @var array<array-key, PhpDocArrayItemFixture>|null */
    public array $nullableArray;

    /** @var array<array-key, PhpDocArrayItemFixture|DateTimeImmutable> */
    public array $compoundItems;

    /** @var PhpDocArrayItemFixture[] */
    #[Subtype('string', limit: 2)]
    public array $subtypedItems;

    /** @var array */
    public array $mixedItems;

    public array $untypedItems;
}
