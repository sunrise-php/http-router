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
final class MediaTypeComparator
{
    public function equals(MediaTypeInterface $a, MediaTypeInterface $b): bool
    {
        return ($a->getType() === $b->getType() || $a->getType() === '*' || $b->getType() === '*')
            && ($a->getSubtype() === $b->getSubtype() || $a->getSubtype() === '*' || $b->getSubtype() === '*');
    }
}
