<?php declare(strict_types=1);

/**
 * It's free open-source software released under the MIT License.
 *
 * @author Anatoly Nekhay <afenric@gmail.com>
 * @copyright Copyright (c) 2018, Anatoly Nekhay
 * @license https://github.com/sunrise-php/http-router/blob/master/LICENSE
 * @link https://github.com/sunrise-php/http-router
 */

namespace Sunrise\Http\Router\Entity;

/**
 * Import functions
 */
use function filter_var;
use function ip2long;

/**
 * Import constants
 */
use const FILTER_FLAG_IPV4;
use const FILTER_FLAG_IPV6;
use const FILTER_FLAG_NO_PRIV_RANGE;
use const FILTER_FLAG_NO_RES_RANGE;
use const FILTER_VALIDATE_IP;

/**
 * IP address entity
 *
 * @since 3.0.0
 */
final class IpAddress
{

    /**
     * The IP address value
     *
     * @var string
     */
    private string $value;

    /**
     * Constructor of the class
     *
     * @param string $value
     */
    public function __construct(string $value)
    {
        $this->value = $value;
    }

    /**
     * Gets the IP address value
     *
     * @return string
     */
    public function getValue(): string
    {
        return $this->value;
    }

    /**
     * Checks if the IP address is valid
     *
     * @return bool
     */
    public function isValid(): bool
    {
        return false !== filter_var($this->value, FILTER_VALIDATE_IP);
    }

    /**
     * Checks if the IP address is IPv4
     *
     * @return bool
     */
    public function isVersion4(): bool
    {
        return false !== filter_var($this->value, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4);
    }

    /**
     * Checks if the IP address is IPv6
     *
     * @return bool
     */
    public function isVersion6(): bool
    {
        return false !== filter_var($this->value, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6);
    }

    /**
     * Checks if the IP address is in the private range
     *
     * @return bool
     */
    public function isInPrivateRange(): bool
    {
        if (!$this->isValid()) {
            return false;
        }

        return false === filter_var($this->value, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE);
    }

    /**
     * Checks if the IP address is in the reserved range
     *
     * @return bool
     */
    public function isInReservedRange(): bool
    {
        if (!$this->isValid()) {
            return false;
        }

        return false === filter_var($this->value, FILTER_VALIDATE_IP, FILTER_FLAG_NO_RES_RANGE);
    }

    /**
     * Converts the IP address to an integer if it possible
     *
     * @return int|null
     */
    public function toLong(): ?int
    {
        if (!$this->isVersion4()) {
            return null;
        }

        $long = ip2long($this->value);
        if ($long === false) {
            return null;
        }

        return $long;
    }

    /**
     * Converts the object to a string
     *
     * @return string
     */
    public function __toString(): string
    {
        return $this->value;
    }
}
