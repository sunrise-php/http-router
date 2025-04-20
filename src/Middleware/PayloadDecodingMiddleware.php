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
use Sunrise\Coder\CodecManagerInterface;
use Sunrise\Coder\Exception\CodecException;
use Sunrise\Http\Router\Dictionary\HeaderName;
use Sunrise\Http\Router\Exception\HttpExceptionFactory;
use Sunrise\Http\Router\ServerRequest;
use Sunrise\Http\Router\StringableMediaType;

use function array_map;

/**
 * @since 3.0.0
 */
final class PayloadDecodingMiddleware implements MiddlewareInterface
{
    /**
     * @param array<array-key, mixed> $codecContext
     */
    public function __construct(
        private readonly CodecManagerInterface $codecManager,
        private readonly array $codecContext = [],
    ) {
    }

    /**
     * @inheritDoc
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $serverRequest = ServerRequest::create($request);

        $serverConsumedMediaTypes = $serverRequest->getRoute()->getConsumedMediaTypes();
        if ($serverConsumedMediaTypes === []) {
            // The server expects nothing from the client, just keep going...
            return $handler->handle($request);
        }

        $clientProducedMediaType = $serverRequest->getClientProducedMediaType();
        if ($clientProducedMediaType === null) {
            throw HttpExceptionFactory::missingMediaType();
        }

        if (!$serverRequest->serverConsumesMediaType($clientProducedMediaType)) {
            throw HttpExceptionFactory::unsupportedMediaType()
                ->addHeaderField(HeaderName::ACCEPT, ...array_map(
                    StringableMediaType::create(...),
                    $serverConsumedMediaTypes,
                ));
        }

        if (!$this->codecManager->supportsMediaType($clientProducedMediaType)) {
            return $handler->handle($request);
        }

        try {
            /** @var array<array-key, mixed>|object|null $parsedBody */
            $parsedBody = $this->codecManager->decode(
                $clientProducedMediaType,
                (string) $request->getBody(),
                $this->codecContext,
            );
        } catch (CodecException $e) {
            throw HttpExceptionFactory::invalidBody(previous: $e);
        }

        return $handler->handle($request->withParsedBody($parsedBody));
    }
}
