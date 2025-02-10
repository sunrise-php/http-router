<?php

declare(strict_types=1);

namespace Sunrise\Http\Router\Tests\Fixture\App\Dto\Product;

use DateTimeImmutable;
use DateTimeZone;
use Ramsey\Uuid\UuidInterface;
use Sunrise\Http\Router\Tests\Fixture\App\Config\DomainAgreement;
use Sunrise\Http\Router\Tests\Fixture\App\Dictionary\ProductFeature;
use Sunrise\Http\Router\Tests\Fixture\App\Dictionary\ProductStatus;
use Sunrise\Hydrator\Annotation\Alias;
use Sunrise\Hydrator\Annotation\DefaultValue;
use Sunrise\Hydrator\Annotation\Format;
use Sunrise\Hydrator\Annotation\Ignore;
use Sunrise\Hydrator\Annotation\Subtype;

final class ProductCreateRequest
{
    // this is an empty array for OA, because it's an interface.
    public readonly \Iterator $iterator;
    // this is an empty array for OA, because it's a non-instantiable class.
    public readonly \SplHeap $splHeap;
    // this is an empty array for OA, because it's an internal class.
    public readonly \stdClass $stdClass;

    public readonly int $publicId;

    public function __construct(
        public readonly string $name,
        // this is bad, just for testing.
        public readonly float $price,
        public readonly UuidInterface $categoryId,
        public readonly ProductTagDtoCollection $tags,
        #[Subtype(ProductFeature::class, limit: 2)]
        public readonly array $features,
        public readonly ProductStatus $status,
        public readonly bool $isModerated,
        #[Alias('timezone')]
        public readonly DateTimeZone $clientTimezone,
        #[Format(DomainAgreement::DEFAULT_INPUT_TIMESTAMP_FORMAT)]
        // support for php7, not recommended in the context of this library.
        #[DefaultValue(new DateTimeImmutable('now'))]
        public readonly DateTimeImmutable $createdAt,
        #[Ignore]
        public readonly string $permanentProperty = 'value',
    ) {
    }
}
