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
 * Exception
 */
class Exception extends RuntimeException implements ExceptionInterface
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
     * @param string $message
     * @param array $context
     * @param int $code
     * @param Throwable|null $previous
     *
     * @link https://www.php.net/manual/en/exception.construct.php
     */
    public function __construct(string $message = '', array $context = [], int $code = 0, ?Throwable $previous = null)
    {
        $this->context = $context;

        parent::__construct($message, $code, $previous);
    }

    /**
     * {@inheritdoc}
     */
    final public function getContext() : array
    {
        return $this->context;
    }

    /**
     * {@inheritdoc}
     */
    final public function fromContext($key, $default = null)
    {
        return $this->context[$key] ?? $default;
    }
}
