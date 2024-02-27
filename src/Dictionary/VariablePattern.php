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

namespace Sunrise\Http\Router\Dictionary;

/**
 * @since 3.0.0
 */
final class VariablePattern
{
    public const ALNUM = '[0-9A-Za-z]+';
    public const ALPHA = '[A-Za-z]+';
    public const DIGIT = '[0-9]+';
    public const LOWER = '[a-z]+';
    public const UPPER = '[A-Z]+';
    public const WORD = '[0-9A-Za-z_]+';
    public const SLUG = '[0-9A-Za-z-]+';
    public const SLUG_UTF8 = '[\p{N}\p{L}-]+';
    public const UUID = '[0-9A-Fa-f]{8}-[0-9A-Fa-f]{4}-[0-9A-Fa-f]{4}-[0-9A-Fa-f]{4}-[0-9A-Fa-f]{12}';
}
