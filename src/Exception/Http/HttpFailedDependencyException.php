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
use Throwable;

/**
 * HTTP Failed Dependency Exception
 *
 * The request failed due to failure of a previous request.
 *
 * @since 3.0.0
 */
class HttpFailedDependencyException extends HttpException
{

    /**
     * Constructor of the class
     *
     * @param ?string $message
     * @param int $code
     * @param ?Throwable $previous
     */
    public function __construct(?string $message = null, int $code = 0, ?Throwable $previous = null)
    {
        $message ??= 'Failed Dependency';

        parent::__construct(self::STATUS_FAILED_DEPENDENCY, $message, $code, $previous);
    }
}