<?php declare(strict_types=1);

/**
 * It's free open-source software released under the MIT License.
 *
 * @author Anatoly Fenric <anatoly@fenric.ru>
 * @copyright Copyright (c) 2018, Anatoly Fenric
 * @license https://github.com/sunrise-php/http-router/blob/master/LICENSE
 * @link https://github.com/sunrise-php/http-router
 */

namespace Sunrise\Http\Router\Exception;

/**
 * Import classes
 */
use RuntimeException;
use Throwable;

/**
 * AbstractException
 */
abstract class AbstractException extends RuntimeException implements ExceptionInterface
{

    /**
     * Context of the exception
     *
     * @var array
     */
    private $context = [];

    /**
     * Constructor of the exception
     *
     * @param string    $message
     * @param array     $context
     * @param int       $code
     * @param Throwable $previous
     */
    public function __construct(string $message = '', array $context = [], int $code = 0, Throwable $previous = null)
    {
        $this->context = $context;

        parent::__construct($message, $code, $previous);
    }

    /**
     * Gets the exception context
     *
     * @return array
     */
    public function getContext() : array
    {
        return $this->context;
    }

    /**
     * Gets data from the exception context for the given key
     *
     * The default value will be returned if is no data
     * in the exception context for the given key.
     *
     * @param string $key
     * @param mixed $default
     *
     * @return mixed
     */
    public function fromContext(string $key, $default = null)
    {
        return $this->context[$key] ?? $default;
    }
}
