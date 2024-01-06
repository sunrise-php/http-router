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
use Sunrise\Http\Router\Validation\ConstraintViolationInterface;
use Throwable;

use function join;

/**
 * The package's base HTTP exception
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
    private array $headerFields = [];

    /**
     * The list of constraint violations associated with the error
     *
     * @var list<ConstraintViolationInterface>
     */
    private array $constraintViolations = [];

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

    /**
     * Adds the given HTTP header field that will be sent to the client
     *
     * @param string $fieldName
     * @param Stringable|string ...$fieldValues
     *
     * @return static
     */
    final public function addHeaderField(string $fieldName, Stringable|string ...$fieldValues): static
    {
        // https://datatracker.ietf.org/doc/html/rfc7230#section-7
        $fieldValue = join(', ', $fieldValues);

        $this->headerFields[] = [$fieldName, $fieldValue];

        return $this;
    }

    /**
     * Adds the given constraint violation(s) to the error
     *
     * @param ConstraintViolationInterface ...$constraintViolations
     *
     * @return static
     */
    final public function addConstraintViolation(ConstraintViolationInterface ...$constraintViolations): static
    {
        foreach ($constraintViolations as $constraintViolation) {
            $this->constraintViolations[] = $constraintViolation;
        }

        return $this;
    }
}
