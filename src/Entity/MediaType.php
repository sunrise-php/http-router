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
use Sunrise\Http\Router\Dictionary\Charset;

/**
 * @since 3.0.0
 */
final class MediaType implements Stringable
{

    /**
     * Constructor of the class
     *
     * @param string $type
     * @param string $subtype
     * @param array<string, ?string> $parameters
     */
    public function __construct(private string $type, private string $subtype, private array $parameters = [])
    {
    }

    /**
     * Creates the json media type with the given parameters
     *
     * @param array<string, ?string> $parameters
     *
     * @return self
     */
    public static function json(array $parameters = []): self
    {
        return new self('application', 'json', $parameters);
    }

    /**
     * Creates the xml media type with the given parameters
     *
     * @param array<string, ?string> $parameters
     *
     * @return self
     */
    public static function xml(array $parameters = []): self
    {
        return new self('application', 'xml', $parameters);
    }

    /**
     * Creates the yaml media type with the given parameters
     *
     * @param array<string, ?string> $parameters
     *
     * @return self
     */
    public static function yaml(array $parameters = []): self
    {
        return new self('application', 'yaml', $parameters);
    }

    /**
     * Creates the html media type with the given parameters
     *
     * @param array<string, ?string> $parameters
     *
     * @return self
     */
    public static function html(array $parameters = []): self
    {
        return new self('text', 'html', $parameters);
    }

    /**
     * Creates the text media type with the given parameters
     *
     * @param array<string, ?string> $parameters
     *
     * @return self
     */
    public static function text(array $parameters = []): self
    {
        return new self('text', 'plain', $parameters);
    }

    /**
     * Creates the image media range with the given parameters
     *
     * @param array<string, ?string> $parameters
     *
     * @return self
     */
    public static function image(array $parameters = []): self
    {
        return new self('image', Charset::WILDCARD, $parameters);
    }

    /**
     * Gets the media range type
     *
     * @return string
     */
    public function getType(): string
    {
        return $this->type;
    }

    /**
     * Gets the media range subtype
     *
     * @return string
     */
    public function getSubtype(): string
    {
        return $this->subtype;
    }

    /**
     * Gets the media type parameters
     *
     * @return array<string, ?string>
     */
    public function getParameters(): array
    {
        return $this->parameters;
    }

    /**
     * Gets the media type parameter's value by its given name or
     * returns the given default value if the parameter doesn't exist or is empty
     *
     * <code>
     *     $mediaType->getParameter('q', '1.0');
     * </code>
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
     * Checks if this media type equals to the given media type
     *
     * @param MediaType $other
     *
     * @return bool
     */
    public function equals(MediaType $other): bool
    {
        return ($this->type === Charset::WILDCARD || $other->type === Charset::WILDCARD || $this->type === $other->type)
            // phpcs:ignore Generic.Files.LineLength
            && ($this->subtype === Charset::WILDCARD || $other->subtype === Charset::WILDCARD || $this->subtype === $other->subtype);
    }

    /**
     * @inheritDoc
     */
    public function __toString(): string
    {
        return $this->type . '/' . $this->subtype;
    }
}
