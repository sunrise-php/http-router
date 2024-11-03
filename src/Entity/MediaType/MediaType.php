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

namespace Sunrise\Http\Router\Entity\MediaType;

/**
 * @since 3.0.0
 */
final class MediaType implements MediaTypeInterface
{
    public function __construct(
        private readonly string $identifier,
    ) {
    }

    public static function create(string $identifier): self
    {
        return new self(identifier: $identifier);
    }

    public function getIdentifier(): string
    {
        return $this->identifier;
    }
}
