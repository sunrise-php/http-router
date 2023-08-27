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
 * ResponseResolvedEvent
 *
 * @since 3.0.0
 */
final class ResponseResolvedEvent extends AbstractEvent
{

    /**
     * Constructor of the class
     *
     * @param ReflectionFunction|ReflectionMethod $source
     * @param ServerRequestInterface $request
     * @param ResponseInterface $response
     */
    public function __construct(
        private ReflectionFunction|ReflectionMethod $source,
        private ServerRequestInterface $request,
        private ResponseInterface $response,
    ) {
    }

    /**
     * @return ReflectionMethod|ReflectionFunction
     */
    public function getSource(): ReflectionMethod|ReflectionFunction
    {
        return $this->source;
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
     * @param ResponseInterface $response
     *
     * @return void
     */
    public function setResponse(ResponseInterface $response): void
    {
        $this->response = $response;
    }
}
