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

namespace Sunrise\Http\Router\ParameterResolver;

use Generator;
use Psr\Http\Message\ServerRequestInterface;
use ReflectionAttribute;
use ReflectionNamedType;
use ReflectionParameter;
use Sunrise\Http\Router\Annotation\ProxyChain;
use Sunrise\Http\Router\Entity\ClientRemoteAddress;
use Sunrise\Http\Router\Exception\LogicException;
use Sunrise\Http\Router\ServerRequest;

/**
 * ClientRemoteAddressParameterResolver
 *
 * @since 3.0.0
 */
final class ClientRemoteAddressParameterResolver implements ParameterResolverInterface
{

    /**
     * Constructor of th class
     *
     * @param array<TKey, TValue> $proxyChain
     *
     * @template TKey as non-empty-string Proxy address
     * @template TValue as non-empty-string Trusted header
     */
    public function __construct(private array $proxyChain = [])
    {
    }

    /**
     * @inheritDoc
     *
     * @throws LogicException If the resolver is used incorrectly.
     */
    public function resolveParameter(ReflectionParameter $parameter, mixed $context): Generator
    {
        $type = $parameter->getType();

        if (! ($type instanceof ReflectionNamedType) ||
            ! ($type->getName() === ClientRemoteAddress::class)) {
            return;
        }

        if (! $context instanceof ServerRequestInterface) {
            throw new LogicException(
                'At this level of the application, any operations with the request are not possible.'
            );
        }

        $proxyChain = $this->proxyChain;
        /** @var list<ReflectionAttribute<ProxyChain>> $attributes */
        $attributes = $parameter->getAttributes(ProxyChain::class);
        if (isset($attributes[0])) {
            $proxyChain = $attributes[0]->newInstance()->value;
        }

        yield ServerRequest::from($context)->getClientRemoteAddress($proxyChain);
    }
}
