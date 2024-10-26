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

namespace Sunrise\Http\Router\Middleware;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

use function array_walk_recursive;
use function is_array;
use function is_string;
use function trim;

/**
 * @since 3.0.0
 */
final class WhitespaceTrimmingMiddleware implements MiddlewareInterface
{
    /**
     * @var callable
     *
     * @readonly
     */
    private $walker;

    /**
     * @param null|callable(string):string $trimmer
     */
    public function __construct(?callable $trimmer = null)
    {
        $trimmer ??= trim(...);

        $this->walker = static function (mixed &$value) use ($trimmer): void {
            if (is_string($value)) {
                $value = $trimmer($value);
            }
        };
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $parsedBody = $request->getParsedBody();
        if ($parsedBody !== [] && is_array($parsedBody)) {
            array_walk_recursive($parsedBody, $this->walker);
            $request = $request->withParsedBody($parsedBody);
        }

        $queryParams = $request->getQueryParams();
        if ($queryParams !== []) {
            array_walk_recursive($queryParams, $this->walker);
            $request = $request->withQueryParams($queryParams);
        }

        return $handler->handle($request);
    }
}
