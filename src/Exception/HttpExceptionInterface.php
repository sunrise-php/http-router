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

/**
 * Base HTTP exception interface
 *
 * @since 3.0.0
 */
interface HttpExceptionInterface extends ExceptionInterface, StatusCodeInterface
{

    /**
     * Gets HTTP status code
     *
     * @return int<100, 599>
     */
    public function getStatusCode(): int;

    /**
     * Gets HTTP header fields
     *
     * @return list<array{0: string, 1: string}>
     */
    public function getHeaderFields(): array;
}
