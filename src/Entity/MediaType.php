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
 * Media type
 *
 * @since 3.0.0
 */
final class MediaType
{
    public const APPLICATION_JSON = 'application/json';
    public const APPLICATION_XML = 'application/xml';
    public const TEXT_XML = 'text/xml';

    /**
     * Constructor of the class
     *
     * @param non-empty-string $value
     * @param array<non-empty-string, string> $parameters
     */
    public function __construct(private string $value, private array $parameters = [])
    {
    }

    /**
     * Gets the media type value
     *
     * @return non-empty-string
     */
    public function getValue(): string
    {
        return $this->value;
    }

    /**
     * Gets the media type parameters
     *
     * @return array<non-empty-string, string>
     */
    public function getParameters(): array
    {
        return $this->parameters;
    }
}
