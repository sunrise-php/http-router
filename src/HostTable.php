<?php declare(strict_types=1);

/**
 * It's free open-source software released under the MIT License.
 *
 * @author Anatoly Nekhay <afenric@gmail.com>
 * @copyright Copyright (c) 2018, Anatoly Nekhay
 * @license https://github.com/sunrise-php/http-router/blob/master/LICENSE
 * @link https://github.com/sunrise-php/http-router
 */

namespace Sunrise\Http\Router;

/**
 * The router host table
 *
 * @since 3.0.0
 */
final class HostTable
{

    /**
     * @var array<string, list<string>>
     */
    private array $hosts = [];

    /**
     * Adds the given alias with its hostnames to the table
     *
     * @param string $alias
     * @param string ...$hostnames
     *
     * @return void
     */
    public function add(string $alias, string ...$hostnames): void
    {
        foreach ($hostnames as $hostname) {
            $this->hosts[$alias][] = $hostname;
        }
    }

    /**
     * Loads the given hosts to the table
     *
     * @param array<string, list<string>> $hosts
     *
     * @return void
     */
    public function load(array $hosts): void
    {
        foreach ($hosts as $alias => $hostnames) {
            foreach ($hostnames as $hostname) {
                $this->hosts[$alias][] = $hostname;
            }
        }
    }

    /**
     * Resolves the given hostname to its alias if it exists in the table otherwise returns null
     *
     * @param string $hostname
     *
     * @return string|null
     */
    public function resolve(string $hostname): ?string
    {
        foreach ($this->hosts as $alias => $hostnames) {
            foreach ($hostnames as $value) {
                if ($hostname === $value) {
                    return $alias;
                }
            }
        }

        return null;
    }
}
