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

namespace Sunrise\Http\Router\ResponseResolving;

use Psr\EventDispatcher\EventDispatcherInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use ReflectionAttribute;
use ReflectionFunction;
use ReflectionMethod;
use Sunrise\Http\Router\Annotation\ResponseHeader;
use Sunrise\Http\Router\Annotation\ResponseStatus;
use Sunrise\Http\Router\Event\ResponseResolvedEvent;
use Sunrise\Http\Router\Exception\LogicException;
use Sunrise\Http\Router\ResponseResolving\ResponseResolver\ResponseResolverInterface;

use function sprintf;

/**
 * ResponseResolutioner
 *
 * @since 3.0.0
 */
final class ResponseResolutioner implements ResponseResolutionerInterface
{

    /**
     * @var list<ResponseResolverInterface>
     */
    private array $resolvers = [];

    /**
     * Constructor of the class
     *
     * @param EventDispatcherInterface|null $eventDispatcher
     */
    public function __construct(private ?EventDispatcherInterface $eventDispatcher = null)
    {
    }

    /**
     * @inheritDoc
     */
    public function addResolver(ResponseResolverInterface ...$resolvers): void
    {
        foreach ($resolvers as $resolver) {
            $this->resolvers[] = $resolver;
        }
    }

    /**
     * @inheritDoc
     *
     * @throws LogicException If the response couldn't be resolved to PSR-7 response.
     */
    public function resolveResponse(
        ServerRequestInterface $request,
        mixed $response,
        ReflectionFunction|ReflectionMethod $responder,
    ) : ResponseInterface {
        if ($response instanceof ResponseInterface) {
            return $this->handleResponse($request, $response, $responder);
        }

        foreach ($this->resolvers as $resolver) {
            $result = $resolver->resolveResponse($request, $response, $responder);
            if ($result instanceof ResponseInterface) {
                return $this->handleResponse($request, $result, $responder);
            }
        }

        throw new LogicException(sprintf(
            'The responder {%s} returned an unsupported response.',
            self::stringifyResponder($responder),
        ));
    }

    /**
     * Handles the given response
     *
     * @param ServerRequestInterface $request
     * @param ResponseInterface $response
     * @param ReflectionFunction|ReflectionMethod $responder
     *
     * @return ResponseInterface
     */
    private function handleResponse(
        ServerRequestInterface $request,
        ResponseInterface $response,
        ReflectionFunction|ReflectionMethod $responder,
    ) : ResponseInterface {
        /** @var list<ReflectionAttribute<ResponseStatus>> $attributes */
        $attributes = $responder->getAttributes(ResponseStatus::class);
        if (isset($attributes[0])) {
            $status = $attributes[0]->newInstance();
            $response = $response->withStatus($status->code, $status->phrase);
        }

        /** @var list<ReflectionAttribute<ResponseHeader>> $attributes */
        $attributes = $responder->getAttributes(ResponseHeader::class);
        foreach ($attributes as $attribute) {
            $header = $attribute->newInstance();
            $response = $response->withHeader($header->name, $header->value);
        }

        if (isset($this->eventDispatcher)) {
            $event = new ResponseResolvedEvent($request, $response, $responder);
            $this->eventDispatcher->dispatch($event);
            $response = $event->getResponse();
        }

        return $response;
    }

    /**
     * Stringifies the given responder
     *
     * @param ReflectionFunction|ReflectionMethod $responder
     *
     * @return string
     */
    public static function stringifyResponder(ReflectionFunction|ReflectionMethod $responder): string
    {
        if ($responder instanceof ReflectionMethod) {
            return sprintf('%s::%s(...)', $responder->getDeclaringClass()->getName(), $responder->getName());
        }

        return sprintf('%s(...)', $responder->getName());
    }
}
