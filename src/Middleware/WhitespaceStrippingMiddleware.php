<?php declare(strict_types=1);

/**
 * It's free open-source software released under the MIT License.
 *
 * @author Anatoly Nekhay <afenric@gmail.com>
 * @copyright Copyright (c) 2018, Anatoly Nekhay
 * @license https://github.com/sunrise-php/http-router/blob/master/LICENSE
 * @link https://github.com/sunrise-php/http-router
 */

namespace Sunrise\Http\Router\Middleware;

/**
 * Import classes
 */
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

/**
 * Import functions
 */
use function array_walk_recursive;
use function is_array;
use function is_string;
use function trim;

/**
 * WhitespaceStrippingMiddleware
 *
 * @since 3.0.0
 */
final class WhitespaceStrippingMiddleware implements MiddlewareInterface
{

    /**
     * {@inheritdoc}
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $parsedBody = $request->getParsedBody();

        if (!empty($parsedBody) && is_array($parsedBody)) {
            /** @psalm-suppress MissingClosureParamType, MixedAssignment */
            array_walk_recursive($parsedBody, static function (&$value): void {
                $value = is_string($value) ? trim($value) : $value;
            });

            $request = $request->withParsedBody($parsedBody);
        }

        return $handler->handle($request);
    }
}
