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

namespace Sunrise\Http\Router\Entity\Locale;

/**
 * @since 3.0.0
 */
final class LocaleComparator implements LocaleComparatorInterface
{
    /**
     * @inheritDoc
     */
    public function compare(LocaleInterface $a, LocaleInterface $b): int
    {
        $aLanguageCode = $a->getLanguageCode();
        if ($aLanguageCode === '*') {
            return 0;
        }

        $bLanguageCode = $b->getLanguageCode();
        if ($bLanguageCode === '*') {
            return 0;
        }

        $languagesCmp = $aLanguageCode <=> $bLanguageCode;
        if ($languagesCmp !== 0) {
            return $languagesCmp;
        }

        $aRegionCode = $a->getRegionCode();
        if ($aRegionCode === null) {
            return 0;
        }

        $bRegionCode = $b->getRegionCode();
        if ($bRegionCode === null) {
            return 0;
        }

        return $aRegionCode <=> $bRegionCode;
    }
}
