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
use Sunrise\Http\Router\Exception\HttpException;
use Sunrise\Http\Router\Exception\HttpExceptionFactory;
use Sunrise\Http\Router\ServerRequest;

/**
 * @since 3.0.0
 */
final class PayloadMediaTypeNegotiationMiddleware implements MiddlewareInterface
{
    /**
     * @inheritDoc
     *
     * @throws HttpException If the request's payload media type isn't supported by the server.
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $serverRequest = ServerRequest::create($request);
        $serverConsumedMediaTypes = $serverRequest->getRoute()->getConsumedMediaTypes();
        if (! $serverRequest->clientProducesMediaType(...$serverConsumedMediaTypes)) {
            if ($serverRequest->getClientProducedMediaType() === null) {
                throw HttpExceptionFactory::missingMediaType()
                    ->addHeaderField('Accept', ...$serverConsumedMediaTypes);
            }

            throw HttpExceptionFactory::unsupportedMediaType()
                ->addMessagePlaceholder('{{ media_type }}', $serverRequest->getClientProducedMediaType())
                ->addHeaderField('Accept', ...$serverConsumedMediaTypes);
        }

        return $handler->handle($request);
    }
}
