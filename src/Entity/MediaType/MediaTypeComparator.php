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
final class MediaTypeComparator implements MediaTypeComparatorInterface
{
    public function compare(MediaTypeInterface $a, MediaTypeInterface $b): int
    {
        if (($a->getType() === $b->getType() || $a->getType() === '*' || $b->getType() === '*') &&
            ($a->getSubtype() === $b->getSubtype() || $a->getSubtype() === '*' || $b->getSubtype() === '*')) {
            return 0;
        }

        return $a->__toString() <=> $b->__toString();
    }
}
