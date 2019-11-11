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
 * MethodNotAllowedException
 */
class MethodNotAllowedException extends RuntimeException implements ExceptionInterface
{

    /**
     * Allowed HTTP methods
     *
     * @var string[]
     */
    private $allowedMethods;

    /**
     * Constructor of the class
     *
     * @param string[]  $allowedMethods
     * @param string    $message
     * @param int       $code
     * @param Throwable $previous
     */
    public function __construct(array $allowedMethods, string $message = '', int $code = 0, Throwable $previous = null)
    {
        $this->allowedMethods = $allowedMethods;

        parent::__construct($message, $code, $previous);
    }

    /**
     * Gets allowed HTTP methods
     *
     * @return string[]
     */
    public function getAllowedMethods() : array
    {
        return $this->allowedMethods;
    }
}
