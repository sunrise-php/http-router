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
    final public static function resourceNotFound(
        ?int $code = null,
        ?string $message = null,
        array $placeholders = [],
        ?Throwable $previous = null,
    ): HttpException {
        return new HttpException(
            code: $code ?? StatusCodeInterface::STATUS_NOT_FOUND,
            message: $message ?? ErrorMessage::RESOURCE_NOT_FOUND,
            placeholders: $placeholders,
            previous: $previous,
        );
    }

    final public static function methodNotAllowed(
        ?int $code = null,
        ?string $message = null,
        array $placeholders = [],
        ?Throwable $previous = null,
    ): HttpException {
        return new HttpException(
            code: $code ?? StatusCodeInterface::STATUS_METHOD_NOT_ALLOWED,
            message: $message ?? ErrorMessage::METHOD_NOT_ALLOWED,
            placeholders: $placeholders,
            previous: $previous,
        );
    }

    final public static function mediaTypeNotSupported(
        ?int $code = null,
        ?string $message = null,
        array $placeholders = [],
        ?Throwable $previous = null,
    ): HttpException {
        return new HttpException(
            code: $code ?? StatusCodeInterface::STATUS_UNSUPPORTED_MEDIA_TYPE,
            message: $message ?? ErrorMessage::MEDIA_TYPE_NOT_SUPPORTED,
            placeholders: $placeholders,
            previous: $previous,
        );
    }

    final public static function pathVariableInvalid(
        ?int $code = null,
        ?string $message = null,
        array $placeholders = [],
        ?Throwable $previous = null,
    ): HttpException {
        return new HttpException(
            code: $code ?? StatusCodeInterface::STATUS_BAD_REQUEST,
            message: $message ?? ErrorMessage::PATH_VARIABLE_INVALID,
            placeholders: $placeholders,
            previous: $previous,
        );
    }

    final public static function queryParamsInvalid(
        ?int $code = null,
        ?string $message = null,
        array $placeholders = [],
        ?Throwable $previous = null,
    ): HttpException {
        return new HttpException(
            code: $code ?? StatusCodeInterface::STATUS_BAD_REQUEST,
            message: $message ?? ErrorMessage::QUERY_PARAMS_INVALID,
            placeholders: $placeholders,
            previous: $previous,
        );
    }

    final public static function queryParamMissed(
        ?int $code = null,
        ?string $message = null,
        array $placeholders = [],
        ?Throwable $previous = null,
    ): HttpException {
        return new HttpException(
            code: $code ?? StatusCodeInterface::STATUS_BAD_REQUEST,
            message: $message ?? ErrorMessage::QUERY_PARAM_MISSED,
            placeholders: $placeholders,
            previous: $previous,
        );
    }

    final public static function queryParamInvalid(
        ?int $code = null,
        ?string $message = null,
        array $placeholders = [],
        ?Throwable $previous = null,
    ): HttpException {
        return new HttpException(
            code: $code ?? StatusCodeInterface::STATUS_BAD_REQUEST,
            message: $message ?? ErrorMessage::QUERY_PARAM_INVALID,
            placeholders: $placeholders,
            previous: $previous,
        );
    }

    final public static function headerMissed(
        ?int $code = null,
        ?string $message = null,
        array $placeholders = [],
        ?Throwable $previous = null,
    ): HttpException {
        return new HttpException(
            code: $code ?? StatusCodeInterface::STATUS_BAD_REQUEST,
            message: $message ?? ErrorMessage::HEADER_MISSED,
            placeholders: $placeholders,
            previous: $previous,
        );
    }

    final public static function headerInvalid(
        ?int $code = null,
        ?string $message = null,
        array $placeholders = [],
        ?Throwable $previous = null,
    ): HttpException {
        return new HttpException(
            code: $code ?? StatusCodeInterface::STATUS_BAD_REQUEST,
            message: $message ?? ErrorMessage::HEADER_INVALID,
            placeholders: $placeholders,
            previous: $previous,
        );
    }

    final public static function cookieMissed(
        ?int $code = null,
        ?string $message = null,
        array $placeholders = [],
        ?Throwable $previous = null,
    ): HttpException {
        return new HttpException(
            code: $code ?? StatusCodeInterface::STATUS_BAD_REQUEST,
            message: $message ?? ErrorMessage::COOKIE_MISSED,
            placeholders: $placeholders,
            previous: $previous,
        );
    }

    final public static function cookieInvalid(
        ?int $code = null,
        ?string $message = null,
        array $placeholders = [],
        ?Throwable $previous = null,
    ): HttpException {
        return new HttpException(
            code: $code ?? StatusCodeInterface::STATUS_BAD_REQUEST,
            message: $message ?? ErrorMessage::COOKIE_INVALID,
            placeholders: $placeholders,
            previous: $previous,
        );
    }

    final public static function bodyInvalid(
        ?int $code = null,
        ?string $message = null,
        array $placeholders = [],
        ?Throwable $previous = null,
    ): HttpException {
        return new HttpException(
            code: $code ?? StatusCodeInterface::STATUS_BAD_REQUEST,
            message: $message ?? ErrorMessage::BODY_INVALID,
            placeholders: $placeholders,
            previous: $previous,
        );
    }

    final public static function jsonPayloadEmpty(
        ?int $code = null,
        ?string $message = null,
        array $placeholders = [],
        ?Throwable $previous = null,
    ): HttpException {
        return new HttpException(
            code: $code ?? StatusCodeInterface::STATUS_BAD_REQUEST,
            message: $message ?? ErrorMessage::JSON_PAYLOAD_EMPTY,
            placeholders: $placeholders,
            previous: $previous,
        );
    }

    final public static function jsonPayloadInvalid(
        ?int $code = null,
        ?string $message = null,
        array $placeholders = [],
        ?Throwable $previous = null,
    ): HttpException {
        return new HttpException(
            code: $code ?? StatusCodeInterface::STATUS_BAD_REQUEST,
            message: $message ?? ErrorMessage::JSON_PAYLOAD_INVALID,
            placeholders: $placeholders,
            previous: $previous,
        );
    }

    final public static function jsonPayloadFormInvalid(
        ?int $code = null,
        ?string $message = null,
        array $placeholders = [],
        ?Throwable $previous = null,
    ): HttpException {
        return new HttpException(
            code: $code ?? StatusCodeInterface::STATUS_BAD_REQUEST,
            message: $message ?? ErrorMessage::JSON_PAYLOAD_FORM_INVALID,
            placeholders: $placeholders,
            previous: $previous,
        );
    }
}
