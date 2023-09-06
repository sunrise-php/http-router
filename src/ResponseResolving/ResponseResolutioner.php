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

use function get_debug_type;
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
        ReflectionFunction|ReflectionMethod $source,
        ServerRequestInterface $request,
        mixed $response,
    ) : ResponseInterface {
        if ($response instanceof ResponseInterface) {
            return $this->handleResponse($source, $request, $response);
        }

        foreach ($this->resolvers as $resolver) {
            $resolvedResponse = $resolver->resolveResponse($source, $request, $response);
            if ($resolvedResponse instanceof ResponseInterface) {
                return $this->handleResponse($source, $request, $resolvedResponse);
            }
        }

        throw new LogicException(sprintf(
            'Unable to resolve the response {%s->%s} to PSR-7 response.',
            self::stringifySource($source),
            get_debug_type($response),
        ));
    }

    /**
     * Handles the given response
     *
     * @param ReflectionFunction|ReflectionMethod $source
     * @param ServerRequestInterface $request
     * @param ResponseInterface $response
     *
     * @return ResponseInterface
     */
    private function handleResponse(
        ReflectionFunction|ReflectionMethod $source,
        ServerRequestInterface $request,
        mixed $response,
    ) : ResponseInterface {
        /** @var list<ReflectionAttribute<ResponseStatus>> $attributes */
        $attributes = $source->getAttributes(ResponseStatus::class);
        if (isset($attributes[0])) {
            $status = $attributes[0]->newInstance();
            $response = $response->withStatus($status->code);
        }

        /** @var list<ReflectionAttribute<ResponseHeader>> $attributes */
        $attributes = $source->getAttributes(ResponseHeader::class);
        foreach ($attributes as $attribute) {
            $header = $attribute->newInstance();
            $response = $response->withHeader($header->name, $header->value);
        }

        if (isset($this->eventDispatcher)) {
            $event = new ResponseResolvedEvent($source, $request, $response);
            $this->eventDispatcher->dispatch($event);
            $response = $event->getResponse();
        }

        return $response;
    }

    /**
     * Stringifies the given source of a response
     *
     * @param ReflectionFunction|ReflectionMethod $source
     *
     * @return string
     */
    public static function stringifySource(ReflectionFunction|ReflectionMethod $source): string
    {
        if ($source instanceof ReflectionMethod) {
            return sprintf('%s::%s()', $source->getDeclaringClass()->getName(), $source->getName());
        }

        return sprintf('%s()', $source->getName());
    }
}
