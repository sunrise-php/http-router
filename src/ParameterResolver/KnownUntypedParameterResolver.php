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
use Sunrise\Http\Router\ParameterResolverInterface;
use ReflectionParameter;

/**
 * KnownUntypedParameterResolver
 *
 * @since 3.0.0
 */
final class KnownUntypedParameterResolver implements ParameterResolverInterface
{

    /**
     * @var string
     */
    private string $name;

    /**
     * @var mixed
     */
    private $value;

    /**
     * @param string $name
     * @param mixed $value
     */
    public function __construct(string $name, $value)
    {
        $this->name = $name;
        $this->value = $value;
    }

    /**
     * {@inheritdoc}
     */
    public function supportsParameter(ReflectionParameter $parameter, $context): bool
    {
        if ($parameter->hasType()) {
            return false;
        }

        if (!($parameter->getName() === $this->name)) {
            return false;
        }

        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function resolveParameter(ReflectionParameter $parameter, $context)
    {
        return $this->value;
    }
}
