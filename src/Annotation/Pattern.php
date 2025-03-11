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

namespace Sunrise\Http\Router\Annotation;

use Attribute;
use Sunrise\Http\Router\Dictionary\VariablePattern;

/**
 * Pay attention to the {@see VariablePattern} dictionary.
 *
 * @link https://dev.sunrise-studio.io/docs/reference/routing-annotations?id=pattern
 * @since 3.0.0
 */
#[Attribute(Attribute::TARGET_CLASS | Attribute::TARGET_METHOD | Attribute::IS_REPEATABLE)]
class Pattern
{
    public function __construct(
        public readonly string $variableName,
        public readonly string $value,
    ) {
    }
}
