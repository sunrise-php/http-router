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
 * IP address
 *
 * @since 3.0.0
 */
final class IpAddress
{

    /**
     * Constructor of the class
     *
     * @param non-empty-string $value The IP address value
     * @param list<non-empty-string> $proxies The list of proxies in front of this IP address
     */
    public function __construct(private string $value, private array $proxies = [])
    {
    }

    /**
     * Gets the IP address value
     *
     * @return non-empty-string
     */
    public function getValue(): string
    {
        return $this->value;
    }

    /**
     * Gets the list of proxies in front of this IP address
     *
     * @return list<non-empty-string>
     */
    public function getProxies(): array
    {
        return $this->proxies;
    }
}