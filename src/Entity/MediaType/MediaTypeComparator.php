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
        $aType = $a->getType();
        $aSubtype = $a->getSubtype();

        $bType = $b->getType();
        $bSubtype = $b->getSubtype();

        if (
            ($aType === '*' || $bType === '*' || strcasecmp($aType, $bType) === 0) &&
            ($aSubtype === '*' || $bSubtype === '*' || strcasecmp($aSubtype, $bSubtype) === 0)
        ) {
            return 0;
        }

        return strcasecmp((string) $a, (string) $b) < 0 ? -1 : 1;
    }
}
