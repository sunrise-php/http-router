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
     * @inheritDoc
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $parsedBody = $request->getParsedBody();
        if ($parsedBody !== [] && is_array($parsedBody)) {
            array_walk_recursive($parsedBody, self::trim(...));
            $request = $request->withParsedBody($parsedBody);
        }

        $queryParams = $request->getQueryParams();
        if ($queryParams !== []) {
            array_walk_recursive($queryParams, self::trim(...));
            $request = $request->withQueryParams($queryParams);
        }

        return $handler->handle($request);
    }

    private static function trim(mixed &$value): void
    {
        if (is_string($value)) {
            $value = trim($value);
        }
    }
}
