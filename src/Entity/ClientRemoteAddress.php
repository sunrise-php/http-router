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
 * Client Remote Address
 *
 * @since 3.0.0
 */
final class ClientRemoteAddress implements Stringable
{

    /**
     * Constructor of the class
     *
     * @param non-empty-string $address
     * @param list<non-empty-string> $proxies
     */
    public function __construct(private string $address, private array $proxies = [])
    {
    }

    /**
     * Gets the client's remote address
     *
     * @return non-empty-string
     */
    public function getAddress(): string
    {
        return $this->address;
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
        return $this->address;
    }
}
