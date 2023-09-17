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

use Stringable;

use function join;

/**
 * Language
 *
 * @since 3.0.0
 */
final class Language implements Stringable
{

    /**
     * Constructor of the class
     *
     * @param string $code
     * @param list<string> $subtags
     * @param array<string, ?string> $parameters
     */
    public function __construct(private string $code, private array $subtags = [], private array $parameters = [])
    {
    }

    /**
     * Gets the language code
     *
     * @return string
     */
    public function getCode(): string
    {
        return $this->code;
    }

    /**
     * Gets the language subtags
     *
     * @return list<string>
     */
    public function getSubtags(): array
    {
        return $this->subtags;
    }

    /**
     * Gets the language parameters
     *
     * @return array<string, ?string>
     */
    public function getParameters(): array
    {
        return $this->parameters;
    }

    /**
     * Gets the language parameter's value by its given name or
     * returns the given default value if the parameter doesn't exist or is empty
     *
     * @param string $name
     * @param ?string $default
     *
     * @return ?string
     */
    public function getParameter(string $name, ?string $default = null): ?string
    {
        return isset($this->parameters[$name][0]) ? $this->parameters[$name] : $default;
    }

    /**
     * Checks if this language's code equals to the given language's code
     *
     * Please note that when comparing languages, their subtags are not factored in.
     *
     * @param Language $other
     *
     * @return bool
     */
    public function equals(Language $other): bool
    {
        return $this->code === '*' || $other->code === '*' || $this->code === $other->code;
    }

    /**
     * @inheritDoc
     */
    public function __toString(): string
    {
        return join('-', [$this->code, ...$this->subtags]);
    }
}
