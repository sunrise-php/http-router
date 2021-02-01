<?php declare(strict_types=1);

/**
 * It's free open-source software released under the MIT License.
 *
 * @author Anatoly Fenric <anatoly@fenric.ru>
 * @copyright Copyright (c) 2018, Anatoly Fenric
 * @license https://github.com/sunrise-php/http-router/blob/master/LICENSE
 * @link https://github.com/sunrise-php/http-router
 */

namespace Sunrise\Http\Router\Exception;

/**
 * Import functions
 */
use function is_array;
use function is_int;
use function is_string;
use function is_subclass_of;

/**
 * ArgumentException
 *
 * @since 2.6.0
 */
class ArgumentException extends Exception
{

    /**
     * @param mixed $operand
     * @param string $message
     *
     * @return void
     * @throws static
     */
    public static function assertIsArray($operand, string $message) : void
    {
        if (!is_array($operand)) {
            throw new static($message, [
                'invalidValue' => $operand,
            ]);
        }
    }

    /**
     * @param mixed $operand
     * @param string $message
     *
     * @return void
     * @throws static
     */
    public static function assertIsInteger($operand, string $message) : void
    {
        if (!is_int($operand)) {
            throw new static($message, [
                'invalidValue' => $operand,
            ]);
        }
    }

    /**
     * @param mixed $operand
     * @param string $message
     *
     * @return void
     * @throws static
     */
    public static function assertIsString($operand, string $message) : void
    {
        if (!is_string($operand)) {
            throw new static($message, [
                'invalidValue' => $operand,
            ]);
        }
    }

    /**
     * @param mixed $operand
     * @param string $message
     *
     * @return void
     * @throws static
     */
    public static function assertIsNotEmptyArray($operand, string $message) : void
    {
        if ([] === $operand || !is_array($operand)) {
            throw new static($message, [
                'invalidValue' => $operand,
            ]);
        }
    }

    /**
     * @param mixed $operand
     * @param string $message
     *
     * @return void
     * @throws static
     */
    public static function assertIsNotEmptyString($operand, string $message) : void
    {
        if ('' === $operand || !is_string($operand)) {
            throw new static($message, [
                'invalidValue' => $operand,
            ]);
        }
    }

    /**
     * @param mixed $operand
     * @param string $className
     * @param string $message
     *
     * @return void
     * @throws static
     */
    public static function assertIsSubclassOf($operand, string $className, string $message) : void
    {
        if (!is_subclass_of($operand, $className)) {
            throw new static($message, [
                'invalidValue' => $operand,
            ]);
        }
    }

    /**
     * @param mixed $operand
     * @param string $message
     *
     * @return void
     * @throws static
     */
    public static function assertIsNotEmpty($operand, string $message) : void
    {
        if ('' === $operand || [] === $operand) {
            throw new static($message, [
                'invalidValue' => $operand,
            ]);
        }
    }
}
