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
use Psr\Http\Message\ServerRequestInterface;
use ReflectionFunction;
use ReflectionMethod;
use Sunrise\Http\Router\Entity\MediaType;
use Sunrise\Http\Router\Exception\LogicException;
use Sunrise\Http\Router\ServerRequest;
use Throwable;
use Whoops\Run as Whoops;

use function class_exists;

/**
 * ExceptionResponseResolver
 *
 * @since 3.0.0
 *
 * @link https://github.com/filp/whoops
 */
final class ExceptionResponseResolver implements ResponseResolverInterface
{

    /**
     * Constructor of the class
     *
     * @param ResponseFactoryInterface $responseFactory
     * @param int<100, 599> $defaultResponseStatusCode
     *
     * @throws LogicException If the whoops package isn't installed.
     */
    public function __construct(
        private ResponseFactoryInterface $responseFactory,
        private int $defaultResponseStatusCode = 500,
    ) {
        if (!class_exists(Whoops::class)) {
            throw new LogicException(
                'The whoops package is required, run the `composer require filp/whoops` command to resolve it.'
            );
        }
    }

    /**
     * @inheritDoc
     */
    public function resolveResponse(
        mixed $response,
        ServerRequestInterface $request,
        ReflectionMethod|ReflectionFunction $source,
    ): ?ResponseInterface {
        if (! $response instanceof Throwable) {
            return null;
        }

        $whoops = new Whoops();
        $whoops->allowQuit(false);
        $whoops->writeToOutput(false);

        $clientPreferredMediaType = ServerRequest::from($request)
            ->getClientPreferredMediaType(
                MediaType::html(),
                MediaType::json(),
                MediaType::xml(),
            );

        if ($clientPreferredMediaType->equals(MediaType::html())) {
            $whoops->pushHandler(new \Whoops\Handler\PrettyPageHandler());
        } elseif ($clientPreferredMediaType->equals(MediaType::json())) {
            $whoops->pushHandler(new \Whoops\Handler\JsonResponseHandler());
        } elseif ($clientPreferredMediaType->equals(MediaType::xml())) {
            $whoops->pushHandler(new \Whoops\Handler\XmlResponseHandler());
        }

        $result = $this->responseFactory->createResponse($this->defaultResponseStatusCode)
            ->withHeader('Content-Type', $clientPreferredMediaType->__toString());

        $result->getBody()->write($whoops->handleException($response));

        return $result;
    }
}
