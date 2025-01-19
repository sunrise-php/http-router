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

/**
 * @since 3.0.0
 */
final class Locale implements LocaleInterface
{
    public function __construct(
        private readonly string $languageCode,
        private readonly ?string $regionCode,
    ) {
    }

    public function getLanguageCode(): string
    {
        return $this->languageCode;
    }

    public function getRegionCode(): ?string
    {
        return $this->regionCode;
    }
}
