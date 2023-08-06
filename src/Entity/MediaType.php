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

namespace Sunrise\Http\Router\Entity;

use Sunrise\Http\Router\Dictionary\Charset;

/**
 * Media type
 *
 * @since 3.0.0
 */
final class MediaType
{

    /**
     * Constructor of the class
     *
     * @param non-empty-string $type
     * @param non-empty-string $subtype
     * @param array<non-empty-string, string> $parameters
     */
    public function __construct(private string $type, private string $subtype, private array $parameters = [])
    {
    }

    /**
     * Gets the type of the media type
     *
     * @return non-empty-string
     */
    public function getType(): string
    {
        return $this->type;
    }

    /**
     * Gets the subtype of the media type
     *
     * @return non-empty-string
     */
    public function getSubtype(): string
    {
        return $this->subtype;
    }

    /**
     * Gets the parameters of the media type
     *
     * @return array<non-empty-string, string>
     */
    public function getParameters(): array
    {
        return $this->parameters;
    }

    /**
     * Gets the media range of the media type
     *
     * @return non-empty-string
     */
    public function getMediaRange(): string
    {
        return $this->type . '/' . $this->subtype;
    }

    /**
     * Gets the quality factor of the media type
     *
     * @return float
     */
    public function getQualityFactor(): float
    {
        return (float) ($this->parameters['q'] ?? 1.);
    }

    /**
     * Checks if the type of the media type is wildcard
     *
     * @return bool
     */
    public function isWildcardType(): bool
    {
        return $this->type === Charset::WILDCARD;
    }

    /**
     * Checks if the subtype of the media type is wildcard
     *
     * @return bool
     */
    public function isWildcardSubtype(): bool
    {
        return $this->subtype === Charset::WILDCARD;
    }

    /**
     * Checks if this media type equals to the given media type
     *
     * @param MediaType $other
     *
     * @return bool
     */
    public function equals(MediaType $other): bool
    {
        return ($this->isWildcardType() || $other->isWildcardType() || $this->getType() === $other->getType())
            && ($this->isWildcardSubtype() || $other->isWildcardSubtype() || $this->getType() === $other->getSubtype());
    }
}
