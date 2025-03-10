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

use Stringable;
use Sunrise\Coder\MediaTypeInterface;

/**
 * @since 3.0.0
 */
final class StringableMediaType implements MediaTypeInterface, Stringable
{
    public function __construct(
        private readonly MediaTypeInterface $mediaType,
    ) {
    }

    public static function create(MediaTypeInterface $mediaType): self
    {
        return new self($mediaType);
    }

    public function getIdentifier(): string
    {
        return $this->mediaType->getIdentifier();
    }

    public function __toString(): string
    {
        return $this->getIdentifier();
    }
}
