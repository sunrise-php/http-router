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

namespace Sunrise\Http\Router\Annotation\Pattern;

use Sunrise\Http\Router\Annotation\Pattern;
use Sunrise\Http\Router\Dictionary\VariablePattern;

/**
 * @since 3.0.0
 */
final class Uuid extends Pattern
{
    public function __construct(string $variableName)
    {
        parent::__construct($variableName, VariablePattern::UUID);
    }
}
