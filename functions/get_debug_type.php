<?php declare(strict_types=1);

/**
 * It's free open-source software released under the MIT License.
 *
 * @author Anatoly Nekhay <afenric@gmail.com>
 * @copyright Copyright (c) 2018, Anatoly Nekhay
 * @license https://github.com/sunrise-php/http-router/blob/master/LICENSE
 * @link https://github.com/sunrise-php/http-router
 */

if (!function_exists('get_debug_type')) {

    /**
     * Polyfill for the get_debug_type function
     *
     * @param mixed $value
     *
     * @return string
     *
     * @since 3.0.0
     *
     * @link https://www.php.net/get_debug_type
     */
    function get_debug_type($value): string
    {
        if (null === $value) {
            return 'null';
        }

        if (is_bool($value)) {
            return 'bool';
        }

        if (is_int($value)) {
            return 'int';
        }

        if (is_float($value)) {
            return 'float';
        }

        if (is_object($value)) {
            return get_class($value);
        }

        return gettype($value);
    }
}
