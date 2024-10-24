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

namespace Sunrise\Http\Router\Entity\MediaType;

use Stringable;

/**
 * @since 3.0.0
 */
interface MediaTypeInterface extends Stringable
{
    final public const SEPARATOR = '/';

    public function getType(): string;

    public function getSubtype(): string;
}
