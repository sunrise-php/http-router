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
use Sunrise\Http\Router\Dictionary\HeaderName;
use Sunrise\Http\Router\Dictionary\PlaceholderCode;
use Sunrise\Http\Router\Exception\HttpException;
use Sunrise\Http\Router\Exception\HttpExceptionFactory;
use Sunrise\Http\Router\ServerRequest;
use Sunrise\Http\Router\StringableMediaType;

use function array_map;
use function array_values;

/**
 * @since 3.0.0
 */
final class PayloadNegotiationMiddleware implements MiddlewareInterface
{
    public function __construct(
        private readonly ?int $errorStatusCode = null,
        private readonly ?string $errorMessage = null,
    ) {
    }

    /**
     * @inheritDoc
     *
     * @throws HttpException If the request payload's media type isn't supported by the server.
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $serverRequest = ServerRequest::create($request);

        $serverConsumedMediaTypes = array_values($serverRequest->getRoute()->getConsumedMediaTypes());
        if ($serverConsumedMediaTypes === []) {
            return $handler->handle($request);
        }

        $clientProducedMediaType = $serverRequest->getClientProducedMediaType();
        if ($clientProducedMediaType === null) {
            throw HttpExceptionFactory::missingContentType($this->errorMessage, $this->errorStatusCode)
                ->addHeaderField(HeaderName::ACCEPT, ...array_map(
                    StringableMediaType::create(...),
                    $serverConsumedMediaTypes,
                ));
        }

        if (!$serverRequest->clientProducesMediaType(...$serverConsumedMediaTypes)) {
            throw HttpExceptionFactory::unsupportedMediaType($this->errorMessage, $this->errorStatusCode)
                ->addMessagePlaceholder(PlaceholderCode::MEDIA_TYPE, $clientProducedMediaType->getIdentifier())
                ->addHeaderField(HeaderName::ACCEPT, ...array_map(
                    StringableMediaType::create(...),
                    $serverConsumedMediaTypes,
                ));
        }

        return $handler->handle($request);
    }
}
