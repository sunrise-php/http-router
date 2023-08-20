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
use Sunrise\Http\Router\Annotation\JsonResponseBody;
use Sunrise\Http\Router\Exception\LogicException;
use Sunrise\Http\Router\ResponseResolutioner;

use function extension_loaded;
use function json_encode;
use function sprintf;

use const JSON_PRESERVE_ZERO_FRACTION;
use const JSON_THROW_ON_ERROR;
use const JSON_UNESCAPED_SLASHES;
use const JSON_UNESCAPED_UNICODE;

/**
 * JsonResponseBodyResponseResolver
 *
 * @since 3.0.0
 *
 * @link https://www.php.net/manual/en/book.json.php
 */
final class JsonResponseBodyResponseResolver implements ResponseResolverInterface
{
    public const DEFAULT_JSON_ENCODING_FLAGS = JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRESERVE_ZERO_FRACTION;
    public const DEFAULT_JSON_ENCODING_DEPTH = 512;

    /**
     * Constructor of the class
     *
     * @param ResponseFactoryInterface $responseFactory
     * @param int|null $jsonEncodingFlags
     * @param int|null $jsonEncodingDepth
     */
    public function __construct(
        private ResponseFactoryInterface $responseFactory,
        private ?int $jsonEncodingFlags = null,
        private ?int $jsonEncodingDepth = null,
    ) {
        if (!extension_loaded('json')) {
            throw new LogicException(
                'The JSON extension is required, run the `pecl install json` command to resolve it.'
            );
        }
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

        $jsonResponseBody = $attributes[0]->newInstance();

        $jsonEncodingFlags = $jsonResponseBody->jsonEncodingFlags ?? $this->jsonEncodingFlags ?? self::DEFAULT_JSON_ENCODING_FLAGS;
        $jsonEncodingDepth = $jsonResponseBody->jsonEncodingDepth ?? $this->jsonEncodingDepth ?? self::DEFAULT_JSON_ENCODING_DEPTH;

        try {
            $payload = json_encode($response, $jsonEncodingFlags | JSON_THROW_ON_ERROR, $jsonEncodingDepth);
        } catch (JsonException $e) {
            throw new LogicException(sprintf(
                'Unable to encode a response from the source {%s} due to: %s',
                ResponseResolutioner::stringifySource($source),
                $e->getMessage(),
            ), 0, $e);
        }

        $result = $this->responseFactory->createResponse(StatusCodeInterface::STATUS_OK)
            ->withHeader('Content-Type', 'application/json; charset=UTF-8');

        $result->getBody()->write($payload);

        return $result;
    }
}
