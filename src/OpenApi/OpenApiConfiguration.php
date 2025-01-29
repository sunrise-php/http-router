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

use Fig\Http\Message\StatusCodeInterface;
use Sunrise\Http\Router\Dictionary\MediaType;
use Sunrise\Http\Router\MediaTypeInterface;
use Sunrise\Http\Router\ResponseResolver\EmptyResponseResolver;
use Sunrise\Hydrator\TypeConverter\TimestampTypeConverter;

use function sys_get_temp_dir;

use const DIRECTORY_SEPARATOR;

/**
 * @since 3.0.0
 */
final class OpenApiConfiguration
{
    public const DEFAULT_DOCUMENT_MEDIA_TYPE = MediaType::JSON;
    public const DEFAULT_DOCUMENT_READ_MODE = 'rb';
    public const DEFAULT_TEMPORARY_DOCUMENT_BASENAME = 'openapi';
    public const DEFAULT_TIMESTAMP_FORMAT = TimestampTypeConverter::DEFAULT_FORMAT;
    public const DEFAULT_EMPTY_RESPONSE_STATUS_CODE = EmptyResponseResolver::DEFAULT_STATUS_CODE;
    public const DEFAULT_SUCCESSFUL_RESPONSE_STATUS_CODE = StatusCodeInterface::STATUS_OK;
    public const DEFAULT_SUCCESSFUL_RESPONSE_DESCRIPTION = 'The operation was successful.';
    public const DEFAULT_UNSUCCESSFUL_RESPONSE_DESCRIPTION = 'The operation was unsuccessful.';

    public function __construct(
        /** @var array<array-key, mixed> */
        public readonly array $initialDocument,
        /** @var array<array-key, mixed> */
        public readonly array $initialOperation = [],
        public readonly MediaTypeInterface $documentMediaType = self::DEFAULT_DOCUMENT_MEDIA_TYPE,
        /** @var array<array-key, mixed> */
        public readonly array $documentEncodingContext = [],
        public readonly ?string $documentFilename = null,
        public readonly string $documentReadMode = self::DEFAULT_DOCUMENT_READ_MODE,
        public readonly string $temporaryDocumentBasename = self::DEFAULT_TEMPORARY_DOCUMENT_BASENAME,
        public readonly string $defaultTimestampFormat = self::DEFAULT_TIMESTAMP_FORMAT,
        public readonly int $emptyResponseStatusCode = self::DEFAULT_EMPTY_RESPONSE_STATUS_CODE,
        public readonly int $successfulResponseStatusCode = self::DEFAULT_SUCCESSFUL_RESPONSE_STATUS_CODE,
        public readonly string $successfulResponseDescription = self::DEFAULT_SUCCESSFUL_RESPONSE_DESCRIPTION,
        /** @var class-string|null */
        public readonly ?string $unsuccessfulResponseViewName = null,
        public readonly string $unsuccessfulResponseDescription = self::DEFAULT_UNSUCCESSFUL_RESPONSE_DESCRIPTION,
    ) {
    }

    public function getDocumentFilename(): string
    {
        return $this->documentFilename ?? $this->getTemporaryDocumentFilename();
    }

    public function getTemporaryDocumentFilename(): string
    {
        return sys_get_temp_dir() . DIRECTORY_SEPARATOR . $this->temporaryDocumentBasename;
    }
}
