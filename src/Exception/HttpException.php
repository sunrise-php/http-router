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
use Sunrise\Http\Router\Dto\ViolationDto;
use Sunrise\Hydrator\Exception\InvalidValueException;
use Symfony\Component\Validator\ConstraintViolationInterface;
use Throwable;

use function join;

/**
 * Base HTTP exception
 *
 * @since 3.0.0
 */
class HttpException extends RuntimeException implements HttpExceptionInterface
{

    /**
     * The error's reason phrase
     *
     * @var non-empty-string
     */
    private string $reasonPhrase = 'Something went wrong';

    /**
     * HTTP status code that will be sent to the client
     *
     * @var int<100, 599>
     */
    private int $statusCode;

    /**
     * HTTP header fields that will be sent to the client
     *
     * @var list<array{0: non-empty-string, 1: non-empty-string}>
     */
    private array $headers = [];

    /**
     * The list of violations associated with the error
     *
     * @var list<ViolationDto>
     */
    private array $violations = [];

    /**
     * Constructor of the class
     *
     * @param int<100, 599> $statusCode
     * @param string $message
     * @param int $code
     * @param Throwable|null $previous
     */
    public function __construct(int $statusCode, string $message, int $code = 0, ?Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);

        $this->statusCode = $statusCode;
    }

    /**
     * @inheritDoc
     */
    final public function getReasonPhrase(): string
    {
        return $this->reasonPhrase;
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
    final public function getHeaders(): array
    {
        return $this->headers;
    }

    /**
     * @inheritDoc
     */
    final public function getViolations(): array
    {
        return $this->violations;
    }

    /**
     * Sets the given message to the error
     *
     * @param non-empty-string $message
     *
     * @return static
     */
    final public function setMessage(string $message): static
    {
        $this->message = $message;

        return $this;
    }

    /**
     * Sets the given reason phrase to the error
     *
     * @param non-empty-string $reasonPhrase
     *
     * @return static
     */
    final public function setReasonPhrase(string $reasonPhrase): static
    {
        $this->reasonPhrase = $reasonPhrase;

        return $this;
    }

    /**
     * Sets the given HTTP status code that will be sent to the client
     *
     * @param int<100, 599> $statusCode
     *
     * @return static
     */
    final public function setStatusCode(int $statusCode): static
    {
        $this->statusCode = $statusCode;

        return $this;
    }

    /**
     * Adds the given HTTP header field that will be sent to the client
     *
     * @param non-empty-string $fieldName
     * @param Stringable|non-empty-string ...$fieldValues
     *
     * @return static
     */
    final public function addHeader(string $fieldName, Stringable|string ...$fieldValues): static
    {
        /** @var non-empty-string $fieldValue */
        $fieldValue = join(', ', $fieldValues);

        $this->headers[] = [$fieldName, $fieldValue];

        return $this;
    }

    /**
     * Adds the given violation(s) associated with the error
     *
     * @param ViolationDto ...$violations
     *
     * @return static
     */
    final public function addViolation(ViolationDto ...$violations): static
    {
        foreach ($violations as $violation) {
            $this->violations[] = $violation;
        }

        return $this;
    }

    /**
     * Adds the given hydrator violation(s) associated with the error
     *
     * @param InvalidValueException ...$violations
     *
     * @return static
     */
    final public function addHydratorViolation(InvalidValueException ...$violations): static
    {
        foreach ($violations as $violation) {
            $this->violations[] = ViolationDto::fromHydratorViolation($violation);
        }

        return $this;
    }

    /**
     * Adds the given validator violation(s) associated with the error
     *
     * @param ConstraintViolationInterface ...$violations
     *
     * @return static
     */
    final public function addValidatorViolation(ConstraintViolationInterface ...$violations): static
    {
        foreach ($violations as $violation) {
            $this->violations[] = ViolationDto::fromValidatorViolation($violation);
        }

        return $this;
    }
}
