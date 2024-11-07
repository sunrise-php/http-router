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

use function explode;
use function strtolower;

/**
 * @since 3.0.0
 */
final class MediaTypeComparator implements MediaTypeComparatorInterface
{
    /**
     * @inheritDoc
     */
    public function compare(MediaTypeInterface $a, MediaTypeInterface $b): int
    {
        $aId = $a->getIdentifier();
        if ($aId === '*/*') {
            return 0;
        }

        $bId = $b->getIdentifier();
        if ($bId === '*/*') {
            return 0;
        }

        $aId = strtolower($aId);
        $bId = strtolower($bId);

        if ($aId === $bId) {
            return 0;
        }

        $aParts = explode('/', $aId, 2);
        $aParts[1] ??= '';

        $bParts = explode('/', $bId, 2);
        $bParts[1] ??= '';

        $sameTypes = $aParts[0] === $bParts[0] || $aParts[0] === '*' || $bParts[0] === '*';
        $sameSubtypes = $aParts[1] === $bParts[1] || $aParts[1] === '*' || $bParts[1] === '*';

        return ($sameTypes && $sameSubtypes) ? 0 : $aId <=> $bId;
    }
}
