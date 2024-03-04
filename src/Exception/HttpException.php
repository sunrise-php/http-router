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
use RuntimeException;
use Stringable;
use Sunrise\Http\Router\ConstraintViolationInterface;
use Sunrise\Http\Router\Dictionary\ErrorMessage;
use Throwable;

use function join;
use function strtr;

/**
 * @since 3.0.0
 */
class HttpException extends RuntimeException implements HttpExceptionInterface
{
    /**
     * The exception's non-interpolated message.
     */
    private string $messageTemplate;

    /**
     * Placeholders for interpolating the exception's non-interpolated message.
     *
     * @var array<array-key, mixed>
     */
    private array $messagePlaceholders = [];

    /**
     * @var list<array{0: string, 1: string}>
     */
    private array $headerFields = [];

    /**
     * @var list<ConstraintViolationInterface>
     */
    private array $constraintViolations = [];

    public function __construct(int $code, string $message, array $placeholders = [], ?Throwable $previous = null)
    {
        $this->messageTemplate = $message;
        $this->messagePlaceholders = $placeholders;

        $interpolatedMessage = strtr($message, $placeholders);

        parent::__construct($interpolatedMessage, $code, $previous);
    }

    final public static function resourceNotFound(
        ?int $code = null,
        ?string $message = null,
        array $placeholders = [],
        ?Throwable $previous = null,
    ): self {
        return new self(
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
    ): self {
        return new self(
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
    ): self {
        return new self(
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
    ): self {
        return new self(
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
    ): self {
        return new self(
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
    ): self {
        return new self(
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
    ): self {
        return new self(
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
    ): self {
        return new self(
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
    ): self {
        return new self(
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
    ): self {
        return new self(
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
    ): self {
        return new self(
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
    ): self {
        return new self(
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
    ): self {
        return new self(
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
    ): self {
        return new self(
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
    ): self {
        return new self(
            code: $code ?? StatusCodeInterface::STATUS_BAD_REQUEST,
            message: $message ?? ErrorMessage::JSON_PAYLOAD_FORM_INVALID,
            placeholders: $placeholders,
            previous: $previous,
        );
    }

    /**
     * @inheritDoc
     */
    final public function getMessageTemplate(): string
    {
        return $this->messageTemplate;
    }

    /**
     * @inheritDoc
     */
    final public function getMessagePlaceholders(): array
    {
        return $this->messagePlaceholders;
    }

    /**
     * @inheritDoc
     */
    final public function getHeaderFields(): array
    {
        return $this->headerFields;
    }

    /**
     * @inheritDoc
     */
    final public function getConstraintViolations(): array
    {
        return $this->constraintViolations;
    }

    final public function addMessagePlaceholder(string $placeholder, mixed $replacement): static
    {
        $this->messagePlaceholders[$placeholder] = $replacement;

        $this->message = strtr($this->messageTemplate, $this->messagePlaceholders);

        return $this;
    }

    final public function addHeaderField(string $fieldName, string|Stringable ...$fieldValues): static
    {
        // https://datatracker.ietf.org/doc/html/rfc7230#section-7
        $fieldValue = join(', ', $fieldValues);

        $this->headerFields[] = [$fieldName, $fieldValue];

        return $this;
    }

    final public function addConstraintViolation(ConstraintViolationInterface ...$constraintViolations): static
    {
        foreach ($constraintViolations as $constraintViolation) {
            $this->constraintViolations[] = $constraintViolation;
        }

        return $this;
    }
}
