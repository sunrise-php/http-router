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

use RuntimeException;
use Stringable;
use Sunrise\Http\Router\ConstraintViolationInterface;
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
    private array $messagePlaceholders;

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
