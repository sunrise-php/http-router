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
use Sunrise\Http\Router\Validation\ConstraintViolation\HydratorConstraintViolationAdapter;
use Sunrise\Http\Router\Validation\ConstraintViolation\ValidatorConstraintViolationAdapter;
use Sunrise\Http\Router\Validation\ConstraintViolationInterface;
use Throwable;

use function array_map;
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
     * @var array<string, mixed>
     */
    private array $messagePlaceholders = [];

    /**
     * @var array<array-key, array{0: string, 1: string}>
     */
    private array $headerFields = [];

    /**
     * @var array<array-key, ConstraintViolationInterface>
     */
    private array $constraintViolations = [];

    public function __construct(string $message, int $code, ?Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);

        $this->messageTemplate = $message;
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
        $this->message = strtr($this->message, [$placeholder => $replacement]);

        $this->messagePlaceholders[$placeholder] = $replacement;

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

    final public function addHydratorConstraintViolation(
        \Sunrise\Hydrator\Exception\InvalidValueException ...$constraintViolations
    ): static {
        return $this->addConstraintViolation(...array_map(
            HydratorConstraintViolationAdapter::create(...),
            $constraintViolations,
        ));
    }

    final public function addValidatorConstraintViolation(
        \Symfony\Component\Validator\ConstraintViolationInterface ...$constraintViolations
    ): static {
        return $this->addConstraintViolation(...array_map(
            ValidatorConstraintViolationAdapter::create(...),
            $constraintViolations,
        ));
    }
}
