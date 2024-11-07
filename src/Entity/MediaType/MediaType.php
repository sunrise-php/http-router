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

/**
 * @since 3.0.0
 */
final class MediaType implements MediaTypeInterface
{
    public const IDENTIFIER_JSON = 'application/json';
    public const IDENTIFIER_XML = 'application/xml';

    public function __construct(
        private readonly string $identifier,
    ) {
    }

    public static function json(): self
    {
        return new self(self::IDENTIFIER_JSON);
    }

    public static function xml(): self
    {
        return new self(self::IDENTIFIER_XML);
    }

    public function getIdentifier(): string
    {
        return $this->identifier;
    }
}
