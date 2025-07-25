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
use Psr\Http\Message\StreamFactoryInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

/**
 * @since 3.2.0
 */
final class Base64DecodingMiddleware implements MiddlewareInterface
{
    public function __construct(
        private readonly StreamFactoryInterface $streamFactory,
    ) {
    }

    /**
     * @inheritDoc
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        // https://developer.mozilla.org/en-US/docs/Web/HTTP/Reference/Headers/Content-Encoding
        if ($request->getHeaderLine('Content-Encoding') === 'base64') {
            $resource = $request->getBody()->detach();
            // https://www.php.net/manual/en/filters.convert.php
            \stream_filter_append($resource, 'convert.base64-decode');

            $body = $this->streamFactory->createStreamFromResource($resource);
            $request = $request->withBody($body);
        }

        return $handler->handle($request);
    }
}
