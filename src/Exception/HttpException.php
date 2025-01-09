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
use Sunrise\Http\Router\Validation\ConstraintViolationInterface;
use Throwable;

use function implode;
use function strtr;

/**
 * @since 3.0.0
 */
class HttpException extends RuntimeException implements StatusCodeInterface
{
    /**
     * The exception's non-interpolated message.
     */
    private string $messageTemplate;

    /**
     * @var array<string, mixed>
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

    public function __construct(string $message, int $code, ?Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);

        $this->messageTemplate = $message;
    }

    /**
     * Returns the exception's <b>non-interpolated</b> message.
     */
    final public function getMessageTemplate(): string
    {
        return $this->messageTemplate;
    }

    /**
     * @return array<string, mixed>
     */
    final public function getMessagePlaceholders(): array
    {
        return $this->messagePlaceholders;
    }

    /**
     * @return list<array{0: string, 1: string}>
     */
    final public function getHeaderFields(): array
    {
        return $this->headerFields;
    }

    /**
     * @return list<ConstraintViolationInterface>
     */
    final public function getConstraintViolations(): array
    {
        return $this->constraintViolations;
    }

    final public function addMessagePlaceholder(string $placeholder, mixed $replacement): static
    {
        $this->message = strtr($this->message, [$placeholder => $replacement]);

        $this->messagePlaceholders[$placeholder] = $replacement;

        return $this;
    }

    final public function addHeaderField(string $fieldName, string|Stringable ...$fieldValues): static
    {
        // https://datatracker.ietf.org/doc/html/rfc7230#section-7
        $fieldValue = implode(', ', $fieldValues);

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
