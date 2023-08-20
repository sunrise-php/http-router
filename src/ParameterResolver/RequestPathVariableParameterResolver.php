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
use ReflectionAttribute;
use ReflectionParameter;
use Sunrise\Http\Router\Annotation\RequestPathVariable;

final class RequestPathVariableParameterResolver implements ParameterResolverInterface
{

    /**
     * @inheritDoc
     */
    public function resolveParameter(ReflectionParameter $parameter, mixed $context): Generator
    {
        /** @var list<ReflectionAttribute<RequestPathVariable>> $attributes */
        $attributes = $parameter->getAttributes(RequestPathVariable::class);
        if ($attributes === []) {
            return;
        }

        // TODO: Implement the method...
    }
}
