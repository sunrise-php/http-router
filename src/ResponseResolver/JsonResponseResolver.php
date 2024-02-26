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
use function json_encode;
use function sprintf;
use const JSON_THROW_ON_ERROR;

/**
 * JsonResponseResolver
 *
 * @since 3.0.0
 */
final class JsonResponseResolver implements ResponseResolverInterface
{

    /**
     * Constructor of the class
     *
     * @param ResponseFactoryInterface $responseFactory
     */
    public function __construct(private ResponseFactoryInterface $responseFactory)
    {
    }

    /**
     * @inheritDoc
     */
    public function resolveResponse(
        ServerRequestInterface $request,
        mixed $response,
        ReflectionFunction|ReflectionMethod $responder,
    ) : ?ResponseInterface {
        /** @var ReflectionAttribute $attributes */
        $attributes = $responder->getAttributes(JsonResponse::class);
        if ($attributes === []) {
            return null;
        }

        $attribute = $attributes[0]->newInstance();

        try {
            /**
             * Ignores the depth...
             * @psalm-suppress ArgumentTypeCoercion
             * @phpstan-ignore-next-line
             */
            $payload = json_encode($response, $attribute->flags | JSON_THROW_ON_ERROR, $attribute->depth);
        } catch (JsonException $e) {
            throw new LogicException(sprintf(
                'The responder {%s} returned a response that could not be encoded to JSON due to: %s',
                ResponseResolver::stringifyResponder($responder),
                $e->getMessage(),
            ));
        }

        $result = $this->responseFactory->createResponse(200);
        $result = $result->withHeader('Content-Type', 'application/json; charset=UTF-8');
        $result->getBody()->write($payload);

        return $result;
    }
}
