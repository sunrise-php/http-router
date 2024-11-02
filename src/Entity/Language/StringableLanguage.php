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

use Stringable;

/**
 * @since 3.0.0
 */
final class StringableLanguage implements LanguageInterface, Stringable
{
    public function __construct(private readonly LanguageInterface $language)
    {
    }

    public static function create(LanguageInterface $language): self
    {
        return new self($language);
    }

    public function getCode(): string
    {
        return $this->language->getCode();
    }

    public function __toString(): string
    {
        return $this->getCode();
    }
}
