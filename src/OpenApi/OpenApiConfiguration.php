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

use Sunrise\Http\Router\ResponseResolver\NullResponseResolver;
use Sunrise\Hydrator\TypeConverter\TimestampTypeConverter;

use function sys_get_temp_dir;

/**
 * @since 3.0.0
 */
final class OpenApiConfiguration
{
    public const DEFAULT_NULL_RESPONSE_STATUS_CODE = NullResponseResolver::DEFAULT_STATUS_CODE;
    public const DEFAULT_TIMESTAMP_FORMAT = TimestampTypeConverter::DEFAULT_FORMAT;
    public const DEFAULT_COMPLETED_OPERATION_DESCRIPTION = 'Operation completed successfully.';

    public function __construct(
        public readonly array $blankDocument,
        public readonly int $defaultNullResponseStatusCode = self::DEFAULT_NULL_RESPONSE_STATUS_CODE,
        public readonly string $defaultTimestampFormat = self::DEFAULT_TIMESTAMP_FORMAT,
        public readonly string $completedOperationDescription = self::DEFAULT_COMPLETED_OPERATION_DESCRIPTION,
        private readonly ?string $documentFilename = null,
    ) {
    }

    public function getDocumentFilename(): string
    {
        return $this->documentFilename ?? sys_get_temp_dir() . '/openapi.json';
    }
}
