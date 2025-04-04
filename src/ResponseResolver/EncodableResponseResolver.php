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

namespace Sunrise\Http\Router\ResponseResolver;

use Fig\Http\Message\StatusCodeInterface;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use ReflectionAttribute;
use ReflectionMethod;
use RuntimeException;
use Sunrise\Coder\CodecManagerInterface;
use Sunrise\Coder\Exception\CodecException;
use Sunrise\Coder\MediaTypeInterface;
use Sunrise\Http\Router\Annotation\EncodableResponse;
use Sunrise\Http\Router\Dictionary\HeaderName;
use Sunrise\Http\Router\ResponseResolverChain;
use Sunrise\Http\Router\ResponseResolverInterface;
use Sunrise\Http\Router\ServerRequest;

use function sprintf;

/**
 * @since 3.0.0
 */
final class EncodableResponseResolver implements ResponseResolverInterface
{
    public function __construct(
        private readonly ResponseFactoryInterface $responseFactory,
        private readonly CodecManagerInterface $codecManager,
        private readonly MediaTypeInterface $defaultMediaType,
        /** @var array<array-key, mixed> */
        private readonly array $codecContext = [],
    ) {
    }

    /**
     * @inheritDoc
     *
     * @throws RuntimeException
     */
    public function resolveResponse(
        mixed $response,
        ReflectionMethod $responder,
        ServerRequestInterface $request,
    ): ?ResponseInterface {
        /** @var list<ReflectionAttribute<EncodableResponse>> $annotations */
        $annotations = $responder->getAttributes(EncodableResponse::class);
        if ($annotations === []) {
            return null;
        }

        $serverRequest = ServerRequest::create($request);
        $serverProducedMediaTypes = $serverRequest->getRoute()->getProducedMediaTypes();
        $clientPreferredMediaType = $serverRequest->getClientPreferredMediaType(...$serverProducedMediaTypes);

        $processParams = $annotations[0]->newInstance();
        $codecMediaType = $clientPreferredMediaType ?? $processParams->defaultMediaType ?? $this->defaultMediaType;
        $codecContext = $processParams->codecContext + $this->codecContext;

        try {
            $encodedResponse = $this->codecManager->encode($codecMediaType, $response, $codecContext);
        } catch (CodecException $e) {
            throw new RuntimeException(sprintf(
                'The responder "%s" returned a response that could not be encoded due to: %s',
                ResponseResolverChain::stringifyResponder($responder),
                $e->getMessage(),
            ), previous: $e);
        }

        $responseContentType = $codecMediaType->getIdentifier();
        $responseContentType .= '; charset=UTF-8'; // It should be a text data type...

        $resolvedResponse = $this->responseFactory
            ->createResponse(StatusCodeInterface::STATUS_OK)
            ->withHeader(HeaderName::CONTENT_TYPE, $responseContentType);

        $resolvedResponse->getBody()->write($encodedResponse);

        return $resolvedResponse;
    }

    public function getWeight(): int
    {
        return 10;
    }
}
