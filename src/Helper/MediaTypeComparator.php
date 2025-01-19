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

namespace Sunrise\Http\Router\Helper;

use Sunrise\Http\Router\MediaTypeInterface;

use function explode;
use function strtolower;

/**
 * @since 3.0.0
 */
final class MediaTypeComparator
{
    /**
     * @return int<-1, 1>
     */
    public static function compareMediaTypes(MediaTypeInterface $a, MediaTypeInterface $b): int
    {
        $aId = strtolower($a->getIdentifier());
        $aParts = explode('/', $aId, 2);
        $aParts[1] ??= '';

        $bId = strtolower($b->getIdentifier());
        $bParts = explode('/', $bId, 2);
        $bParts[1] ??= '';

        $sameTypes = $aParts[0] === $bParts[0] || $aParts[0] === '*' || $bParts[0] === '*';
        $sameSubtypes = $aParts[1] === $bParts[1] || $aParts[1] === '*' || $bParts[1] === '*';

        return $sameTypes && $sameSubtypes ? 0 : $aId <=> $bId;
    }
}
