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

/**
 * @since 3.0.0
 */
final class ServerMediaType implements MediaTypeInterface
{
    public function __construct(
        private readonly string $type,
        private readonly string $subtype,
    ) {
    }

    public static function json(): self
    {
        return new self('application', 'json');
    }

    public static function xml(): self
    {
        return new self('application', 'xml');
    }

    public static function html(): self
    {
        return new self('text', 'html');
    }

    public static function text(): self
    {
        return new self('text', 'plain');
    }

    public static function image(): self
    {
        return new self('image', '*');
    }

    public static function fromString(string $string): self
    {
        $parts = explode(self::SEPARATOR, $string, 2);

        $type = isset($parts[0][0]) ? $parts[0] : '*';
        $subtype = isset($parts[1][0]) ? $parts[1] : '*';

        return new self($type, $subtype);
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function getSubtype(): string
    {
        return $this->subtype;
    }

    public function __toString(): string
    {
        return $this->type . $this::SEPARATOR . $this->subtype;
    }
}
