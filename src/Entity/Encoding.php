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

namespace Sunrise\Http\Router\Entity;

/**
 * @since 3.0.0
 */
final class Encoding implements EncodingInterface
{
    public function __construct(
        private readonly string $method,
        /** @var array<string, string> */
        private readonly array $parameters = [],
    ) {
    }

    public function getMethod(): string
    {
        return $this->method;
    }

    /**
     * @return array<string, string>
     */
    public function getParameters(): array
    {
        return $this->parameters;
    }
}
