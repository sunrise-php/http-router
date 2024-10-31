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

use function strcasecmp;

/**
 * @since 3.0.0
 */
final class MediaTypeComparator implements MediaTypeComparatorInterface
{
    /**
     * @link https://datatracker.ietf.org/doc/html/rfc2045#section-5.1
     */
    public function compare(MediaTypeInterface $a, MediaTypeInterface $b): int
    {
        if (($a->getType() === '*' || $b->getType() === '*' || strcasecmp($a->getType(), $b->getType()) === 0) &&
            ($a->getSubtype() === '*' || $b->getSubtype() === '*' || strcasecmp($a->getSubtype(), $b->getSubtype()) === 0)) {
            return 0;
        }

        return strcasecmp($a->__toString(), $b->__toString()) < 0 ? -1 : 1;
    }
}
