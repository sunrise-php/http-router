<?php declare(strict_types=1);

/**
 * It's free open-source software released under the MIT License.
 *
 * @author Anatoly Nekhay <afenric@gmail.com>
 * @copyright Copyright (c) 2018, Anatoly Nekhay
 * @license https://github.com/sunrise-php/http-router/blob/master/LICENSE
 * @link https://github.com/sunrise-php/http-router
 */

namespace Sunrise\Http\Router\Exception\Http;

/**
 * Import classes
 */
use Fig\Http\Message\StatusCodeInterface;
use Sunrise\Http\Router\Exception\ExceptionInterface;

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
     * @return int
     */
    public function getStatusCode(): int;
}
