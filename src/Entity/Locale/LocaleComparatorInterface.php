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
interface LocaleComparatorInterface
{
    /**
     * @return int<-1, 1>
     */
    public function compare(LocaleInterface $a, LocaleInterface $b): int;
}
