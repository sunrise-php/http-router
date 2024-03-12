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
final class ClientLanguage implements LanguageInterface
{
    public function __construct(
        private readonly string $code,
        // One of the Accept-Language header's identifiers:
        // https://datatracker.ietf.org/doc/html/rfc7231#section-5.3.5
        private readonly string $locale,
        /** @var array<string, string> */
        private readonly array $parameters,
    ) {
    }

    public function getCode(): string
    {
        return $this->code;
    }

    public function getLocale(): string
    {
        return $this->locale;
    }

    /**
     * @return array<string, string>
     */
    public function getParameters(): array
    {
        return $this->parameters;
    }
}
