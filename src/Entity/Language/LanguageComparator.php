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

namespace Sunrise\Http\Router\Entity\Language;

/**
 * @since 3.0.0
 */
final class LanguageComparator implements LanguageComparatorInterface
{
    public function compare(LanguageInterface $a, LanguageInterface $b): int
    {
        $aCode = $a->getCode();
        $bCode = $b->getCode();

        if ($aCode === $bCode || $aCode === '*' || $bCode === '*') {
            return 0;
        }

        return $aCode <=> $bCode;
    }
}
