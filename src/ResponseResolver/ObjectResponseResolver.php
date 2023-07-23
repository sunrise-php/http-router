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
use Sunrise\Http\Router\Annotation\ResponseBody;
use ReflectionClass;

use function is_object;

/**
 * ObjectResponseResolver
 *
 * @since 3.0.0
 */
final class ObjectResponseResolver implements ResponseResolverInterface
{
    public function __construct(
        private ResponseFactoryInterface $responseFactory,
    ) {
    }

    /**
     * {@inheritdoc}
     */
    public function supportsResponse($response, $context): bool
    {
        if (!is_object($response)) {
            return false;
        }

        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function resolveResponse($response, $context): ResponseInterface
    {
        $attributes = (new ReflectionClass($response))->getAttributes(ResponseBody::class);

        return $this->responseFactory->createResponse(200);
    }
}
