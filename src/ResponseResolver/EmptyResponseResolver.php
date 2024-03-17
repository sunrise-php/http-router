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
use ReflectionFunction;
use ReflectionMethod;
use Sunrise\Http\Router\Annotation\EmptyResponse;
use Sunrise\Http\Router\ResponseResolverInterface;

/**
 * @since 3.0.0
 */
final class EmptyResponseResolver implements ResponseResolverInterface
{
    public const DEFAULT_STATUS_CODE = StatusCodeInterface::STATUS_NO_CONTENT;

    public function __construct(
        private readonly ResponseFactoryInterface $responseFactory,
        private readonly ?int $defaultStatusCode = null,
    ) {
    }

    public function resolveResponse(
        mixed $response,
        ReflectionMethod|ReflectionFunction $responder,
        ServerRequestInterface $request,
    ): ?ResponseInterface {
        if ($responder->getAttributes(EmptyResponse::class) === []) {
            return null;
        }

        $statusCode = $this->defaultStatusCode ?? self::DEFAULT_STATUS_CODE;

        return $this->responseFactory->createResponse($statusCode);
    }

    public function getWeight(): int
    {
        return 0;
    }
}
