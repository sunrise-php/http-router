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
use Exception;
use Throwable;

/**
 * Base HTTP exception
 *
 * @since 3.0.0
 */
class HttpException extends Exception implements HttpExceptionInterface
{

    /**
     * HTTP status code
     *
     * @var int
     */
    private $statusCode;

    /**
     * Constructor of the class
     *
     * @param int $statusCode
     * @param string $message
     * @param int $errorCode
     * @param ?Throwable $previous
     */
    public function __construct(int $statusCode, string $message, int $errorCode = 0, ?Throwable $previous = null)
    {
        parent::__construct($message, $errorCode, $previous);

        $this->statusCode = $statusCode;
    }

    /**
     * {@inheritdoc}
     */
    final public function getStatusCode(): int
    {
        return $this->statusCode;
    }
}
