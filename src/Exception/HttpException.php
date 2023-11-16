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
use Sunrise\Http\Router\Dictionary\ErrorSource;
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
     * HTTP status code that will be sent to the client
     *
     * @var int<100, 599>
     */
    private int $statusCode;

    /**
     * HTTP header fields that will be sent to the client
     *
     * @var list<array{0: string, 1: string}>
     */
    private array $headers = [];

    /**
     * The error's source
     *
     * @var string
     */
    private string $source = ErrorSource::CLIENT_REQUEST;

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
    final public function getSource(): string
    {
        return $this->source;
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
     * @param string $message
     *
     * @return static
     */
    final public function setMessage(string $message): static
    {
        $this->message = $message;

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
     * @param string $fieldName
     * @param Stringable|string ...$fieldValues
     *
     * @return static
     */
    final public function addHeader(string $fieldName, Stringable|string ...$fieldValues): static
    {
        $fieldValue = join(', ', $fieldValues);

        $this->headers[] = [$fieldName, $fieldValue];

        return $this;
    }

    /**
     * Sets the given source to the error
     *
     * @param string $source
     *
     * @return static
     */
    final public function setSource(string $source): static
    {
        $this->source = $source;

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
