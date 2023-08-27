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

use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use ReflectionFunction;
use ReflectionMethod;

/**
 * EmptyResponseResolver
 *
 * @since 3.0.0
 */
final class EmptyResponseResolver implements ResponseResolverInterface
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
        ReflectionFunction|ReflectionMethod $source,
        ServerRequestInterface $request,
        mixed $response,
    ) : ?ResponseInterface {
        if ($response === null) {
            return $this->responseFactory->createResponse(204);
        }

        return null;
    }
}
