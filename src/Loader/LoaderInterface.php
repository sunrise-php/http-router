<?php

/**
 * It's free open-source software released under the MIT License.
 *
 * @author Anatolii Nekhai <afenric@gmail.com>
 * @copyright Copyright (c) 2018, Anatolii Nekhai
 * @license https://github.com/sunrise-php/http-router/blob/master/LICENSE
 * @link https://github.com/sunrise-php/http-router
 */

declare(strict_types=1);

namespace Sunrise\Http\Router\Loader;

use Sunrise\Http\Router\RouteInterface;

/**
 * @since 2.0.0
 */
interface LoaderInterface
{
    /**
     * @return iterable<array-key, RouteInterface>
     */
    public function load(): iterable;
}
