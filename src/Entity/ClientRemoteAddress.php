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

/**
 * Client remote address
 *
 * @since 3.0.0
 */
final class ClientRemoteAddress implements Stringable
{

    /**
     * Constructor of the class
     *
     * @param non-empty-string $value The address value
     * @param list<non-empty-string> $proxies The list of proxies in front of this address
     */
    public function __construct(private string $value, private array $proxies = [])
    {
    }

    /**
     * Gets the address value
     *
     * @return non-empty-string
     */
    public function getValue(): string
    {
        return $this->value;
    }

    /**
     * Gets the list of proxies in front of this address
     *
     * @return list<non-empty-string>
     */
    public function getProxies(): array
    {
        return $this->proxies;
    }

    /**
     * @inheritDoc
     */
    public function __toString(): string
    {
        return $this->value;
    }
}
