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

namespace Sunrise\Http\Router\Exception\Http;

use Sunrise\Http\Router\Exception\HttpException;
use Throwable;

use function join;

/**
 * HTTP Method Not Allowed Exception
 *
 * @link https://developer.mozilla.org/en-US/docs/Web/HTTP/Status/405
 *
 * @since 3.0.0
 */
class HttpMethodNotAllowedException extends HttpException
{

    /**
     * Allowed HTTP methods
     *
     * @var list<string>
     */
    private array $allowedMethods;

    /**
     * Constructor of the class
     *
     * @param list<string> $allowedMethods
     * @param string|null $message
     * @param int $code
     * @param Throwable|null $previous
     */
    public function __construct(
        array $allowedMethods,
        ?string $message = null,
        int $code = 0,
        ?Throwable $previous = null
    ) {
        $message ??= 'Method Not Allowed';

        parent::__construct(self::STATUS_METHOD_NOT_ALLOWED, $message, $code, $previous);

        $this->allowedMethods = $allowedMethods;

        $this->addHeaderField('Allow', $this->getJoinedAllowedMethods());
    }

    /**
     * Gets allowed HTTP methods
     *
     * @return list<string>
     */
    final public function getAllowedMethods(): array
    {
        return $this->allowedMethods;
    }

    /**
     * Gets joined allowed HTTP methods
     *
     * @return string
     */
    final public function getJoinedAllowedMethods(): string
    {
        return join(',', $this->allowedMethods);
    }
}
