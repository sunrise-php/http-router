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

use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use function is_int;

/**
 * StatusCodeResponseResolver
 *
 * @since 3.0.0
 */
final class StatusCodeResponseResolver implements ResponseResolverInterface
{

    /**
     * @var ResponseFactoryInterface
     */
    private ResponseFactoryInterface $responseFactory;

    /**
     * @param ResponseFactoryInterface $responseFactory
     */
    public function __construct(ResponseFactoryInterface $responseFactory)
    {
        $this->responseFactory = $responseFactory;
    }

    /**
     * @inheritDoc
     */
    public function supportsResponse($response, $context): bool
    {
        return is_int($response) && $response >= 100 && $response <= 599;
    }

    /**
     * @inheritDoc
     */
    public function resolveResponse($response, $context): ResponseInterface
    {
        /** @var int<100, 599> $response */

        return $this->responseFactory->createResponse($response);
    }
}