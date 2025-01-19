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

namespace Sunrise\Http\Router\Middleware;

use Closure;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

use function array_walk_recursive;
use function is_array;
use function is_string;
use function trim;

/**
 * @since 3.0.0
 */
final class StringTrimmingMiddleware implements MiddlewareInterface
{
    /**
     * @var Closure(string): string
     */
    private readonly Closure $trimmer;

    /**
     * @param (Closure(string): string)|null $trimmer
     */
    public function __construct(?Closure $trimmer = null)
    {
        $this->trimmer = $trimmer ?? trim(...);
    }

    /**
     * @inheritDoc
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $queryParams = $request->getQueryParams();
        if ($queryParams !== []) {
            $request = $request->withQueryParams(
                self::trimArray($queryParams, $this->trimmer)
            );
        }

        $parsedBody = $request->getParsedBody();
        if ($parsedBody !== [] && is_array($parsedBody)) {
            $request = $request->withParsedBody(
                self::trimArray($parsedBody, $this->trimmer)
            );
        }

        return $handler->handle($request);
    }

    /**
     * @param Closure(string): string $trimmer
     */
    private static function trimArray(array $array, Closure $trimmer): array
    {
        $walker = static function (mixed &$value) use ($trimmer): void {
            if (is_string($value)) {
                $value = $trimmer($value);
            }
        };

        array_walk_recursive($array, $walker);

        return $array;
    }
}
