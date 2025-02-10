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
    public $qRakBUXnx34V;
    public readonly int|float $jk3K7mWSraZf;
    public readonly \Iterator $FcAkjf2ZaTdv;
    public readonly \SplHeap $kaVKjQFSEdUm;
    public readonly \stdClass $dKQLn8yyMsYG;

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
