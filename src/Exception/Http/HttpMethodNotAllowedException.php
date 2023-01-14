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
 * Import functions
 */
use function join;

/**
 * HTTP Method Not Allowed Exception
 *
 * The request method is known by the server but is not supported by the target resource. For example, an API may not
 * allow calling DELETE to remove a resource.
 *
 * @link https://developer.mozilla.org/en-US/docs/Web/HTTP/Status/405
 *
 * @since 3.0.0
 */
class HttpMethodNotAllowedException extends HttpException
{

    /**
     * Unallowed HTTP method
     *
     * @var string
     */
    private string $unallowedMethod;

    /**
     * Allowed HTTP methods
     *
     * @var list<string>
     */
    private array $allowedMethods = [];

    /**
     * Constructor of the class
     *
     * @param string $unallowedMethod
     * @param string[] $allowedMethods
     * @param ?string $message
     * @param int $code
     * @param ?Throwable $previous
     */
    public function __construct(
        string $unallowedMethod,
        array $allowedMethods,
        ?string $message = null,
        int $code = 0,
        ?Throwable $previous = null
    ) {
        $message ??= 'Method Not Allowed';

        parent::__construct(self::STATUS_METHOD_NOT_ALLOWED, $message, $code, $previous);

        $this->unallowedMethod = $unallowedMethod;
        foreach ($allowedMethods as $allowedMethod) {
            $this->allowedMethods[] = $allowedMethod;
        }
    }

    /**
     * Gets unallowed HTTP method
     *
     * @return string
     */
    final public function getMethod(): string
    {
        return $this->unallowedMethod;
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
        return join(',', $this->getAllowedMethods());
    }

    /**
     * Gets arguments for an Allow header field
     *
     * The server must generate an Allow header field in a 405 status code response.
     *
     * The field must contain a list of methods that the target resource currently supports.
     *
     * Returns an array where key 0 contains the header name and key 1 contains its value.
     *
     * <code>
     *   $response = $response
     *       ->withStatus($e->getStatusCode())
     *       ->withHeader(...$e->getAllowHeaderArguments());
     * </code>
     *
     * @return array{0: string, 1: string}
     */
    final public function getAllowHeaderArguments(): array
    {
        return ['Allow', $this->getJoinedAllowedMethods()];
    }
}
