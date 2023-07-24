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
use Throwable;

/**
 * Base HTTP exception
 *
 * @since 3.0.0
 */
class HttpException extends RuntimeException implements HttpExceptionInterface
{

    /**
     * HTTP status code
     *
     * @var int<100, 599>
     */
    private int $statusCode;

    /**
     * HTTP header fields
     *
     * @var list<array{0: string, 1: string}>
     */
    private array $headerFields = [];

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
     * Adds the given header field to the exception
     *
     * @param string $fieldName
     * @param string $fieldValue
     *
     * @return void
     */
    final public function addHeaderField(string $fieldName, string $fieldValue): void
    {
        $this->headerFields[] = [$fieldName, $fieldValue];
    }
}
