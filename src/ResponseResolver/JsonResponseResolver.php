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
use LogicException;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use ReflectionAttribute;
use ReflectionFunction;
use ReflectionMethod;
use Sunrise\Http\Router\Annotation\JsonResponse;
use Sunrise\Http\Router\ResponseResolver;

use function get_debug_type;
use function json_encode;
use function sprintf;

use const JSON_THROW_ON_ERROR;

/**
 * @since 3.0.0
 */
final class JsonResponseResolver implements ResponseResolverInterface
{
    public function __construct(private readonly ResponseFactoryInterface $responseFactory)
    {
    }

    /**
     * @throws LogicException If the resolver is used incorrectly.
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

        $jsonResponseParams = $annotations[0]->newInstance();

        try {
            /** @psalm-suppress ArgumentTypeCoercion */
            $payload = json_encode($response, $jsonResponseParams->flags | JSON_THROW_ON_ERROR, $jsonResponseParams->depth);
        } catch (JsonException $e) {
            throw new LogicException(sprintf(
                'The responder %s returned the response "%s" that could not be encoded to JSON due to: %s',
                ResponseResolver::stringifyResponder($responder),
                get_debug_type($response),
                $e->getMessage(),
            ), previous: $e);
        }

        $jsonResponse = $this->responseFactory->createResponse(StatusCodeInterface::STATUS_OK)
            ->withHeader('Content-Type', 'application/json; charset=UTF-8');

        $jsonResponse->getBody()->write($payload);

        return $jsonResponse;
    }
}
