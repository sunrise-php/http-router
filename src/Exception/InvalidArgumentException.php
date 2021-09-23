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
 * InvalidArgumentException
 *
 * @since 2.9.0
 */
class InvalidArgumentException extends Exception
{

    /**
     * Gets an invalid value
     *
     * @return mixed
     */
    public function getInvalidValue()
    {
        return $this->fromContext('invalidValue');
    }

    /**
     * Throws the exception if the given operand isn't an array
     *
     * @param mixed $operand
     * @param string $message
     *
     * @return void
     * @throws static
     */
    public static function throwIfNotArray($operand, string $message) : void
    {
        if (!is_array($operand)) {
            throw new static($message, [
                'invalidValue' => $operand,
            ]);
        }
    }

    /**
     * Throws the exception if the given operand isn't an integer
     *
     * @param mixed $operand
     * @param string $message
     *
     * @return void
     * @throws static
     */
    public static function throwIfNotInteger($operand, string $message) : void
    {
        if (!is_int($operand)) {
            throw new static($message, [
                'invalidValue' => $operand,
            ]);
        }
    }

    /**
     * Throws the exception if the given operand isn't a string
     *
     * @param mixed $operand
     * @param string $message
     *
     * @return void
     * @throws static
     */
    public static function throwIfNotString($operand, string $message) : void
    {
        if (!is_string($operand)) {
            throw new static($message, [
                'invalidValue' => $operand,
            ]);
        }
    }

    /**
     * Throws the exception if the given operand doesn't implement the given class
     *
     * @param mixed $operand
     * @param string $className
     * @param string $message
     *
     * @return void
     * @throws static
     */
    public static function throwIfNotImplemented($operand, string $className, string $message) : void
    {
        if (!is_subclass_of($operand, $className)) {
            throw new static($message, [
                'invalidValue' => $operand,
            ]);
        }
    }

    /**
     * Throws the exception if the given operand is an empty
     *
     * @param mixed $operand
     * @param string $message
     *
     * @return void
     * @throws static
     */
    public static function throwIfEmpty($operand, string $message) : void
    {
        if ('' === $operand || [] === $operand) {
            throw new static($message, [
                'invalidValue' => $operand,
            ]);
        }
    }

    /**
     * Throws the exception if the given operand isn't an array or empty
     *
     * @param mixed $operand
     * @param string $message
     *
     * @return void
     * @throws static
     */
    public static function throwIfEmptyArray($operand, string $message) : void
    {
        if ([] === $operand || !is_array($operand)) {
            throw new static($message, [
                'invalidValue' => $operand,
            ]);
        }
    }

    /**
     * Throws the exception if the given operand isn't a string or empty
     *
     * @param mixed $operand
     * @param string $message
     *
     * @return void
     * @throws static
     */
    public static function throwIfEmptyString($operand, string $message) : void
    {
        if ('' === $operand || !is_string($operand)) {
            throw new static($message, [
                'invalidValue' => $operand,
            ]);
        }
    }
}
