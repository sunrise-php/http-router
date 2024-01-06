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

namespace Sunrise\Http\Router\Event;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use ReflectionFunction;
use ReflectionMethod;

/**
 * @since 3.0.0
 */
final class ResponseResolvedEvent extends AbstractEvent
{

    /**
     * Constructor of the class
     *
     * @param ServerRequestInterface $request
     * @param ResponseInterface $response
     * @param ReflectionFunction|ReflectionMethod $responder
     */
    public function __construct(
        private ServerRequestInterface $request,
        private ResponseInterface $response,
        private ReflectionFunction|ReflectionMethod $responder,
    ) {
    }

    /**
     * @return ServerRequestInterface
     */
    public function getRequest(): ServerRequestInterface
    {
        return $this->request;
    }

    /**
     * @return ResponseInterface
     */
    public function getResponse(): ResponseInterface
    {
        return $this->response;
    }

    /**
     * @return ReflectionMethod|ReflectionFunction
     */
    public function getResponder(): ReflectionMethod|ReflectionFunction
    {
        return $this->responder;
    }

    /**
     * @param ResponseInterface $response
     *
     * @return void
     */
    public function setResponse(ResponseInterface $response): void
    {
        $this->response = $response;
    }
}
