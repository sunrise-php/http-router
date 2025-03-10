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

use Sunrise\Http\Router\LanguageInterface;

/**
 * @since 3.0.0
 */
enum Language: string implements LanguageInterface
{
    case English = 'en';

    public function getCode(): string
    {
        return $this->value;
    }
}
