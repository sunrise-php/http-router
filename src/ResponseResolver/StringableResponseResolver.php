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
use LogicException;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use ReflectionFunction;
use ReflectionMethod;
use Stringable;
use Sunrise\Http\Router\Annotation\StringableResponse;
use Sunrise\Http\Router\ResponseResolver;

use function get_debug_type;
use function is_string;
use function sprintf;

/**
 * @since 3.0.0
 */
final class StringableResponseResolver implements ResponseResolverInterface
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
        if ($responder->getAttributes(StringableResponse::class) === []) {
            return null;
        }

        if (!is_string($response) && !($response instanceof Stringable)) {
            throw new LogicException(sprintf(
                'The responder %s returned the response "%s" that cannot be converted to a string.',
                ResponseResolver::stringifyResponder($responder),
                get_debug_type($response),
            ));
        }

        $stringableResponse = $this->responseFactory->createResponse(StatusCodeInterface::STATUS_OK);

        $stringableResponse->getBody()->write((string) $response);

        return $stringableResponse;
    }
}
