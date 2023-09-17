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

namespace Sunrise\Http\Router\Dto;

use Sunrise\Http\Router\Exception\HttpExceptionInterface;

/**
 * @since 3.0.0
 */
final class ErrorDto
{

    /**
     * Constructor of the class
     *
     * @param int<100, 599> $statusCode
     * @param string $reasonPhrase
     * @param string $message
     * @param string $source
     * @param list<ViolationDto> $violations
     */
    public function __construct(
        public int $statusCode,
        public string $reasonPhrase,
        public string $message,
        public string $source,
        public array $violations,
    ) {
    }

    /**
     * Creates the error from the given HTTP error
     *
     * @param HttpExceptionInterface $error
     *
     * @return self
     */
    public static function fromHttpError(HttpExceptionInterface $error): self
    {
        return new self(
            $error->getStatusCode(),
            $error->getReasonPhrase(),
            $error->getMessage(),
            $error->getSource(),
            $error->getViolations(),
        );
    }
}
