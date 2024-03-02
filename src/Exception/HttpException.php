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

use Closure;
use RuntimeException;
use Stringable;
use Sunrise\Http\Router\ConstraintViolationInterface;
use Sunrise\Http\Router\Dictionary\ErrorMessage;
use Throwable;

use function join;
use function strtr;

/**
 * The package's base HTTP exception
 *
 * @since 3.0.0
 */
class HttpException extends RuntimeException implements HttpExceptionInterface
{
    public const DEFAULT_ERROR_STATUS_CODE = 400;

    private string $messageTemplate;

    private array $messagePlaceholders;

    private int $statusCode;

    /**
     * @var list<array{0: string, 1: string}>
     */
    private array $headerFields = [];

    /**
     * @var list<ConstraintViolationInterface>
     */
    private array $constraintViolations = [];

    public function __construct(int $statusCode, string $message, array $placeholders = [], ?Throwable $previous = null)
    {
        parent::__construct(strtr($message, $placeholders), 0, $previous);

        $this->statusCode = $statusCode;
        $this->messageTemplate = $message;
        $this->messagePlaceholders = $placeholders;
    }

    final public static function jsonPayloadEmpty(?int $statusCode = null, ?string $message = null, array $placeholders = [], ?Throwable $previous = null): self
    {
        return new self($statusCode ?? self::DEFAULT_ERROR_STATUS_CODE, $message ?? ErrorMessage::JSON_PAYLOAD_EMPTY, $placeholders, $previous);
    }

    final public static function jsonPayloadInvalid(?int $statusCode = null, ?string $message = null, array $placeholders = [], ?Throwable $previous = null): self
    {
        return new self($statusCode ?? self::DEFAULT_ERROR_STATUS_CODE, $message ?? ErrorMessage::JSON_PAYLOAD_INVALID, $placeholders, $previous);
    }

    final public static function jsonPayloadFormInvalid(?int $statusCode = null, ?string $message = null, array $placeholders = [], ?Throwable $previous = null): self
    {
        return new self($statusCode ?? self::DEFAULT_ERROR_STATUS_CODE, $message ?? ErrorMessage::JSON_PAYLOAD_FORM_INVALID, $placeholders, $previous);
    }

    final public static function bodyInvalid(?int $statusCode = null, ?string $message = null, array $placeholders = [], ?Throwable $previous = null): self
    {
        return new self($statusCode ?? self::DEFAULT_ERROR_STATUS_CODE, $message ?? ErrorMessage::BODY_INVALID, $placeholders, $previous);
    }

    final public static function cookieMissed(?int $statusCode = null, ?string $message = null, array $placeholders = [], ?Throwable $previous = null): self
    {
        return new self($statusCode ?? self::DEFAULT_ERROR_STATUS_CODE, $message ?? ErrorMessage::COOKIE_MISSED, $placeholders, $previous);
    }

    final public static function cookieInvalid(?int $statusCode = null, ?string $message = null, array $placeholders = [], ?Throwable $previous = null): self
    {
        return new self($statusCode ?? self::DEFAULT_ERROR_STATUS_CODE, $message ?? ErrorMessage::COOKIE_INVALID, $placeholders, $previous);
    }

    final public static function headerMissed(?int $statusCode = null, ?string $message = null, array $placeholders = [], ?Throwable $previous = null): self
    {
        return new self($statusCode ?? self::DEFAULT_ERROR_STATUS_CODE, $message ?? ErrorMessage::HEADER_MISSED, $placeholders, $previous);
    }

    final public static function headerInvalid(?int $statusCode = null, ?string $message = null, array $placeholders = [], ?Throwable $previous = null): self
    {
        return new self($statusCode ?? self::DEFAULT_ERROR_STATUS_CODE, $message ?? ErrorMessage::HEADER_INVALID, $placeholders, $previous);
    }

    final public static function queryInvalid(?int $statusCode = null, ?string $message = null, array $placeholders = [], ?Throwable $previous = null): self
    {
        return new self($statusCode ?? self::DEFAULT_ERROR_STATUS_CODE, $message ?? ErrorMessage::QUERY_INVALID, $placeholders, $previous);
    }

    final public static function queryParamMissed(?int $statusCode = null, ?string $message = null, array $placeholders = [], ?Throwable $previous = null): self
    {
        return new self($statusCode ?? self::DEFAULT_ERROR_STATUS_CODE, $message ?? ErrorMessage::QUERY_PARAM_MISSED, $placeholders, $previous);
    }

    final public static function queryParamInvalid(?int $statusCode = null, ?string $message = null, array $placeholders = [], ?Throwable $previous = null): self
    {
        return new self($statusCode ?? self::DEFAULT_ERROR_STATUS_CODE, $message ?? ErrorMessage::QUERY_PARAM_INVALID, $placeholders, $previous);
    }

    final public static function pathVariableInvalid(?int $statusCode = null, ?string $message = null, array $placeholders = [], ?Throwable $previous = null): self
    {
        return new self($statusCode ?? self::DEFAULT_ERROR_STATUS_CODE, $message ?? ErrorMessage::PATH_VARIABLE_INVALID, $placeholders, $previous);
    }

    public function getMessageTemplate(): string
    {
        return $this->messageTemplate;
    }

    public function getMessagePlaceholders(): array
    {
        return $this->messagePlaceholders;
    }

    /**
     * @inheritDoc
     */
    final public function getStatusCode(): int
    {
        return $this->statusCode;
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

    final public function addHeaderField(string $fieldName, Stringable|string ...$fieldValues): static
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
