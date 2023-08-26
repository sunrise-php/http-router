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

namespace Sunrise\Http\Router\ResponseResolving\ResponseResolver;

use JsonException;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use ReflectionAttribute;
use ReflectionFunction;
use ReflectionMethod;
use Sunrise\Http\Router\Annotation\JsonResponseBody;
use Sunrise\Http\Router\Exception\LogicException;
use Sunrise\Http\Router\ResponseResolving\ResponseResolutioner;

use function json_encode;
use function sprintf;

use const JSON_THROW_ON_ERROR;

/**
 * JsonResponseBodyResponseResolver
 *
 * @since 3.0.0
 *
 * @link https://www.php.net/manual/en/book.json.php
 */
final class JsonResponseBodyResponseResolver implements ResponseResolverInterface
{

    /**
     * Constructor of the class
     *
     * @param ResponseFactoryInterface $responseFactory
     * @param int $options
     */
    public function __construct(
        private ResponseFactoryInterface $responseFactory,
        private int $options = 0,
    ) {
    }

    /**
     * @inheritDoc
     *
     * @throws LogicException If the resolver is used incorrectly.
     */
    public function resolveResponse(
        mixed $response,
        ServerRequestInterface $request,
        ReflectionFunction|ReflectionMethod $source,
    ) : ?ResponseInterface {
        /** @var list<ReflectionAttribute<JsonResponseBody>> $attributes */
        $attributes = $source->getAttributes(JsonResponseBody::class);
        if ($attributes === []) {
            return null;
        }

        $attribute = $attributes[0]->newInstance();

        try {
            $payload = json_encode($response, $this->options | $attribute->options | JSON_THROW_ON_ERROR);
        } catch (JsonException $e) {
            throw new LogicException(sprintf(
                'Unable to encode a response from the source {%s} due to: %s',
                ResponseResolutioner::stringifySource($source),
                $e->getMessage(),
            ), previous: $e);
        }

        $result = $this->responseFactory->createResponse(200)
            ->withHeader('Content-Type', 'application/json; charset=UTF-8');

        $result->getBody()->write($payload);

        return $result;
    }
}
