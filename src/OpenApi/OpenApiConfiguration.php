<?php

/**
 * It's free open-source software released under the MIT License.
 *
 * @author Anatoly Nekhay <afenric@gmail.com>
 * @copyright Copyright (c) 2018, Anatoly Nekhay
 * @license https://github.com/sunrise-php/http-router/blob/master/LICENSE
 * @link https://github.com/sunrise-php/http-router
 */

declare(strict_types=1);

namespace Sunrise\Http\Router\OpenApi;

use Sunrise\Http\Router\MediaTypeInterface;
use Sunrise\Hydrator\TypeConverter\TimestampTypeConverter;

use function sys_get_temp_dir;

use const DIRECTORY_SEPARATOR;

/**
 * @since 3.0.0
 */
final class OpenApiConfiguration
{
    public const VERSION = '3.1.1';

    public const DEFAULT_TIMESTAMP_FORMAT = TimestampTypeConverter::DEFAULT_FORMAT;
    public const DEFAULT_RESPONSE_DESCRIPTION = 'The operation was successful.';

    public function __construct(
        /** @var array<array-key, mixed> */
        public readonly array $initialDocument,
        /** @var array<array-key, mixed> */
        public readonly array $initialOperation,
        public readonly MediaTypeInterface $documentMediaType,
        /** @var array<array-key, mixed> */
        public readonly array $documentEncodingContext = [],
        public readonly ?string $documentFilename = null,
        public readonly string $defaultTimestampFormat = self::DEFAULT_TIMESTAMP_FORMAT,
        public readonly string $defaultResponseDescription = self::DEFAULT_RESPONSE_DESCRIPTION,
    ) {
    }

    public function getDocumentFilename(): string
    {
        return $this->documentFilename ?? $this->getTemporaryDocumentFilename();
    }

    public function getTemporaryDocumentFilename(): string
    {
        return sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'openapi';
    }
}
