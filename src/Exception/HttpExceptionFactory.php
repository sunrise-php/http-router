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

namespace Sunrise\Http\Router\Exception;

use Fig\Http\Message\StatusCodeInterface;
use Sunrise\Http\Router\Dictionary\ErrorMessage;
use Throwable;

/**
 * @since 3.0.0
 */
final class HttpExceptionFactory
{
    public static function invalidUri(
        ?string $message = null,
        ?int $code = null,
        ?Throwable $previous = null,
    ): HttpException {
        return new HttpException(
            message: $message ?? ErrorMessage::INVALID_URI,
            code: $code ?? StatusCodeInterface::STATUS_BAD_REQUEST,
            previous: $previous,
        );
    }

    public static function resourceNotFound(
        ?string $message = null,
        ?int $code = null,
        ?Throwable $previous = null,
    ): HttpException {
        return new HttpException(
            message: $message ?? ErrorMessage::RESOURCE_NOT_FOUND,
            code: $code ?? StatusCodeInterface::STATUS_NOT_FOUND,
            previous: $previous,
        );
    }

    public static function methodNotAllowed(
        ?string $message = null,
        ?int $code = null,
        ?Throwable $previous = null,
    ): HttpException {
        return new HttpException(
            message: $message ?? ErrorMessage::METHOD_NOT_ALLOWED,
            code: $code ?? StatusCodeInterface::STATUS_METHOD_NOT_ALLOWED,
            previous: $previous,
        );
    }

    public static function missingMediaType(
        ?string $message = null,
        ?int $code = null,
        ?Throwable $previous = null,
    ): HttpException {
        return new HttpException(
            message: $message ?? ErrorMessage::MISSING_MEDIA_TYPE,
            code: $code ?? StatusCodeInterface::STATUS_UNSUPPORTED_MEDIA_TYPE,
            previous: $previous,
        );
    }

    public static function unsupportedMediaType(
        ?string $message = null,
        ?int $code = null,
        ?Throwable $previous = null,
    ): HttpException {
        return new HttpException(
            message: $message ?? ErrorMessage::UNSUPPORTED_MEDIA_TYPE,
            code: $code ?? StatusCodeInterface::STATUS_UNSUPPORTED_MEDIA_TYPE,
            previous: $previous,
        );
    }

    public static function invalidVariable(
        ?string $message = null,
        ?int $code = null,
        ?Throwable $previous = null,
    ): HttpException {
        return new HttpException(
            message: $message ?? ErrorMessage::INVALID_VARIABLE,
            code: $code ?? StatusCodeInterface::STATUS_BAD_REQUEST,
            previous: $previous,
        );
    }

    public static function invalidQuery(
        ?string $message = null,
        ?int $code = null,
        ?Throwable $previous = null,
    ): HttpException {
        return new HttpException(
            message: $message ?? ErrorMessage::INVALID_QUERY,
            code: $code ?? StatusCodeInterface::STATUS_BAD_REQUEST,
            previous: $previous,
        );
    }

    public static function missingHeader(
        ?string $message = null,
        ?int $code = null,
        ?Throwable $previous = null,
    ): HttpException {
        return new HttpException(
            message: $message ?? ErrorMessage::MISSING_HEADER,
            code: $code ?? StatusCodeInterface::STATUS_BAD_REQUEST,
            previous: $previous,
        );
    }

    public static function invalidHeader(
        ?string $message = null,
        ?int $code = null,
        ?Throwable $previous = null,
    ): HttpException {
        return new HttpException(
            message: $message ?? ErrorMessage::INVALID_HEADER,
            code: $code ?? StatusCodeInterface::STATUS_BAD_REQUEST,
            previous: $previous,
        );
    }

    public static function missingCookie(
        ?string $message = null,
        ?int $code = null,
        ?Throwable $previous = null,
    ): HttpException {
        return new HttpException(
            message: $message ?? ErrorMessage::MISSING_COOKIE,
            code: $code ?? StatusCodeInterface::STATUS_BAD_REQUEST,
            previous: $previous,
        );
    }

    public static function invalidCookie(
        ?string $message = null,
        ?int $code = null,
        ?Throwable $previous = null,
    ): HttpException {
        return new HttpException(
            message: $message ?? ErrorMessage::INVALID_COOKIE,
            code: $code ?? StatusCodeInterface::STATUS_BAD_REQUEST,
            previous: $previous,
        );
    }

    public static function invalidBody(
        ?string $message = null,
        ?int $code = null,
        ?Throwable $previous = null,
    ): HttpException {
        return new HttpException(
            message: $message ?? ErrorMessage::INVALID_BODY,
            code: $code ?? StatusCodeInterface::STATUS_BAD_REQUEST,
            previous: $previous,
        );
    }

    public static function emptyJsonPayload(
        ?string $message = null,
        ?int $code = null,
        ?Throwable $previous = null,
    ): HttpException {
        return new HttpException(
            message: $message ?? ErrorMessage::EMPTY_JSON_PAYLOAD,
            code: $code ?? StatusCodeInterface::STATUS_BAD_REQUEST,
            previous: $previous,
        );
    }

    public static function invalidJsonPayload(
        ?string $message = null,
        ?int $code = null,
        ?Throwable $previous = null,
    ): HttpException {
        return new HttpException(
            message: $message ?? ErrorMessage::INVALID_JSON_PAYLOAD,
            code: $code ?? StatusCodeInterface::STATUS_BAD_REQUEST,
            previous: $previous,
        );
    }

    public static function invalidJsonPayloadForm(
        ?string $message = null,
        ?int $code = null,
        ?Throwable $previous = null,
    ): HttpException {
        return new HttpException(
            message: $message ?? ErrorMessage::INVALID_JSON_PAYLOAD_FORM,
            code: $code ?? StatusCodeInterface::STATUS_BAD_REQUEST,
            previous: $previous,
        );
    }
}
