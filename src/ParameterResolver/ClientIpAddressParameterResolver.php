<?php declare(strict_types=1);

/**
 * It's free open-source software released under the MIT License.
 *
 * @author Anatoly Nekhay <afenric@gmail.com>
 * @copyright Copyright (c) 2018, Anatoly Nekhay
 * @license https://github.com/sunrise-php/http-router/blob/master/LICENSE
 * @link https://github.com/sunrise-php/http-router
 */

namespace Sunrise\Http\Router\ParameterResolver;

/**
 * Import classes
 */
use Psr\Http\Message\ServerRequestInterface;
use Sunrise\Http\Router\Entity\IpAddress;
use Sunrise\Http\Router\ParameterResolverInterface;
use Sunrise\Http\Router\ServerRequest;
use ReflectionNamedType;
use ReflectionParameter;

/**
 * ClientIpAddressParameterResolver
 *
 * @since 3.0.0
 */
final class ClientIpAddressParameterResolver implements ParameterResolverInterface
{

    /**
     * @var array<string, string>
     */
    private array $proxyChain;

    /**
     * Constructor of the class
     *
     * @param array<string, string> $proxyChain
     */
    public function __construct(array $proxyChain = [])
    {
        $this->proxyChain = $proxyChain;
    }

    /**
     * {@inheritdoc}
     */
    public function supportsParameter(ReflectionParameter $parameter, $context): bool
    {
        if (!($context instanceof ServerRequestInterface)) {
            return false;
        }

        if (!($parameter->getType() instanceof ReflectionNamedType)) {
            return false;
        }

        if (!($parameter->getType()->getName() === IpAddress::class)) {
            return false;
        }

        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function resolveParameter(ReflectionParameter $parameter, $context)
    {
        /** @var ServerRequestInterface */
        $context = $context;

        return ServerRequest::from($context)->getClientIpAddress($this->proxyChain);
    }
}
