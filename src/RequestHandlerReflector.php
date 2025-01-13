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

namespace Sunrise\Http\Router;

use Closure;
use InvalidArgumentException;
use Psr\Http\Server\RequestHandlerInterface;
use ReflectionClass;
use ReflectionException;
use ReflectionFunction;
use ReflectionMethod;

use function is_array;
use function is_callable;
use function is_string;
use function is_subclass_of;
use function sprintf;

/**
 * @since 3.0.0
 */
final class RequestHandlerReflector implements RequestHandlerReflectorInterface
{
    /**
     * @inheritDoc
     *
     * @throws InvalidArgumentException
     * @throws ReflectionException
     */
    public function reflectRequestHandler(mixed $reference): ReflectionClass|ReflectionMethod|ReflectionFunction
    {
        if ($reference instanceof RequestHandlerInterface) {
            return new ReflectionClass($reference);
        }

        if ($reference instanceof Closure) {
            return new ReflectionFunction($reference);
        }

        if (is_string($reference) && is_subclass_of($reference, RequestHandlerInterface::class)) {
            return new ReflectionClass($reference);
        }

        // https://github.com/php/php-src/blob/3ed526441400060aa4e618b91b3352371fcd02a8/Zend/zend_API.c#L3884-L3932
        if (is_array($reference) && is_callable($reference, true, $method)) {
            return new ReflectionMethod($method);
        }

        throw new InvalidArgumentException(sprintf(
            'The request handler reference %s could not be reflected.',
            ReferenceResolver::stringifyReference($reference),
        ));
    }
}
