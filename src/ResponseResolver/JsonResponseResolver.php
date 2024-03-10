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
use JsonException;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use ReflectionAttribute;
use ReflectionFunction;
use ReflectionMethod;
use Sunrise\Http\Router\Annotation\JsonResponse;
use Sunrise\Http\Router\Exception\InvalidResponseException;
use Sunrise\Http\Router\ResponseResolver;

use function json_encode;
use function sprintf;

use const JSON_THROW_ON_ERROR;

/**
 * @since 3.0.0
 */
final class JsonResponseResolver implements ResponseResolverInterface
{
    public const DEFAULT_ENCODING_FLAGS = 0;
    public const DEFAULT_ENCODING_DEPTH = 512;

    public function __construct(
        private readonly ResponseFactoryInterface $responseFactory,
        private readonly int $defaultEncodingFlags = self::DEFAULT_ENCODING_FLAGS,
        private readonly int $defaultEncodingDepth = self::DEFAULT_ENCODING_DEPTH,
    ) {
    }

    /**
     * @throws InvalidResponseException
     */
    public function resolveResponse(
        mixed $response,
        ReflectionMethod|ReflectionFunction $responder,
        ServerRequestInterface $request,
    ): ?ResponseInterface {
        /** @var list<ReflectionAttribute<JsonResponse>> $annotations */
        $annotations = $responder->getAttributes(JsonResponse::class);
        if ($annotations === []) {
            return null;
        }

        $processParams = $annotations[0]->newInstance();

        $encodingFlags = $processParams->encodingFlags ?? $this->defaultEncodingFlags;
        $encodingDepth = $processParams->encodingDepth ?? $this->defaultEncodingDepth;

        try {
            /** @psalm-suppress ArgumentTypeCoercion */
            $payload = json_encode($response, $encodingFlags | JSON_THROW_ON_ERROR, $encodingDepth);
        } catch (JsonException $e) {
            throw new InvalidResponseException(sprintf(
                'The responder %s returned a response that could not be encoded to JSON due to: %s',
                ResponseResolver::stringifyResponder($responder),
                $e->getMessage(),
            ), previous: $e);
        }

        $jsonResponse = $this->responseFactory->createResponse(StatusCodeInterface::STATUS_OK)
            ->withHeader('Content-Type', 'application/json; charset=UTF-8');

        $jsonResponse->getBody()->write($payload);

        return $jsonResponse;
    }
}
