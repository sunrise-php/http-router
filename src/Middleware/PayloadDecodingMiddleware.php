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
use Sunrise\Http\Router\CodecManagerInterface;
use Sunrise\Http\Router\Exception\CodecException;
use Sunrise\Http\Router\Exception\HttpException;
use Sunrise\Http\Router\Exception\HttpExceptionFactory;
use Sunrise\Http\Router\ServerRequest;

/**
 * @since 3.0.0
 */
final class PayloadDecodingMiddleware implements MiddlewareInterface
{
    public function __construct(
        private readonly CodecManagerInterface $codecManager,
        private readonly array $codecContext = [],
        private readonly ?int $errorStatusCode = null,
        private readonly ?string $errorMessage = null,
    ) {
    }

    /**
     * @inheritDoc
     *
     * @throws HttpException If the request's payload couldn't be decoded.
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $serverRequest = ServerRequest::create($request);

        $clientProducedMediaType = $serverRequest->getClientProducedMediaType();
        if ($clientProducedMediaType === null) {
            return $handler->handle($request);
        }

        if ($serverRequest->hasRoute() && !$serverRequest->getRoute()->consumesMediaType($clientProducedMediaType)) {
            return $handler->handle($request);
        }

        if (!$this->codecManager->supportsMediaType($clientProducedMediaType)) {
            return $handler->handle($request);
        }

        try {
            $parsedBody = $this->codecManager->decode(
                $clientProducedMediaType,
                (string) $request->getBody(),
                $this->codecContext,
            );
        } catch (CodecException $e) {
            throw HttpExceptionFactory::invalidBody(
                $this->errorMessage,
                $this->errorStatusCode,
                previous: $e,
            );
        }

        return $handler->handle(
            $request->withParsedBody($parsedBody)
        );
    }
}
