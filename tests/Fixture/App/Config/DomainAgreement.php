<?php

declare(strict_types=1);

namespace Sunrise\Http\Router\Tests\Fixture\App\Config;

use Sunrise\Hydrator\TypeConverter\TimestampTypeConverter;

final class DomainAgreement
{
    public const DEFAULT_INPUT_TIMESTAMP_FORMAT = TimestampTypeConverter::DEFAULT_FORMAT;
    public const DEFAULT_PAGE_SIZE = 20;
}
