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

/**
 * Language
 *
 * @since 3.0.0
 */
final class Language
{

    /**
     * Constructor of the class
     *
     * @param string $tag
     * @param list<string> $subtags
     * @param array<string, ?string> $parameters
     */
    public function __construct(private string $tag, private array $subtags = [], private array $parameters = [])
    {
    }

    /**
     * Gets the language's tag
     *
     * @return string
     */
    public function getTag(): string
    {
        return $this->tag;
    }

    /**
     * Gets the language's subtags
     *
     * @return list<string>
     */
    public function getSubtags(): array
    {
        return $this->subtags;
    }

    /**
     * Gets the language's parameters
     *
     * @return array<string, ?string>
     */
    public function getParameters(): array
    {
        return $this->parameters;
    }

    /**
     * Gets the language's quality factor
     *
     * @return float
     */
    public function getQualityFactor(): float
    {
        return (float) ($this->parameters['q'] ?? 1);
    }
}
