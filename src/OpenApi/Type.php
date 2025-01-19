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

namespace Sunrise\Http\Router\OpenApi;

/**
 * @since 3.0.0
 */
final class Type
{
    public const PHP_TYPE_NAME_VOID = 'void';
    public const PHP_TYPE_NAME_NULL = 'null';
    public const PHP_TYPE_NAME_BOOL = 'bool';
    public const PHP_TYPE_NAME_INT = 'int';
    public const PHP_TYPE_NAME_FLOAT = 'float';
    public const PHP_TYPE_NAME_STRING = 'string';
    public const PHP_TYPE_NAME_ARRAY = 'array';
    public const PHP_TYPE_NAME_MIXED = 'mixed';

    public const OAS_TYPE_NAME_BOOLEAN = 'boolean';
    public const OAS_TYPE_NAME_INTEGER = 'integer';
    public const OAS_TYPE_NAME_NUMBER = 'number';
    public const OAS_TYPE_NAME_STRING = 'string';
    public const OAS_TYPE_NAME_ARRAY = 'array';
    public const OAS_TYPE_NAME_OBJECT = 'object';

    public function __construct(
        public readonly string $name,
        public readonly bool $allowsNull,
    ) {
    }
}
