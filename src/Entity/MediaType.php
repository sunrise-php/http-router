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

use function sprintf;

/**
 * Media Type
 *
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
     * Creates the json media type
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
     * Creates the xml media type
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
     * Creates the yaml media type
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
     * Creates the html media type
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
     * Creates the text media type
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
     * Creates the image media range
     *
     * @param array<string, ?string> $parameters
     *
     * @return self
     */
    public static function image(array $parameters = []): self
    {
        return new self('image', '*', $parameters);
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
     * Gets the media type quality factor
     *
     * @return float
     */
    public function getQualityFactor(): float
    {
        return (float) ($this->parameters['q'] ?? 1.);
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
        return ($this->type === '*' || $other->type === '*' || $this->type === $other->type)
            && ($this->subtype === '*' || $other->subtype === '*' || $this->subtype === $other->subtype);
    }

    /**
     * Build the media type
     *
     * @param array<string, ?string> $parameters
     *
     * @return string
     */
    public function build(array $parameters = []): string
    {
        $result = sprintf('%s/%s', $this->type, $this->subtype);
        foreach ($parameters + $this->parameters as $name => $value) {
            $result .= sprintf('; %s="%s"', $name, (string) $value);
        }

        return $result;
    }

    /**
     * @inheritDoc
     */
    public function __toString(): string
    {
        return $this->build();
    }
}
