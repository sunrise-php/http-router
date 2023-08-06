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

/**
 * @since 3.0.0
 */
#[Attribute(Attribute::TARGET_PARAMETER)]
final class ProxyChain
{

    /**
     * Constructor of the class
     *
     * @param array<TKey, TValue> $value
     *
     * @template TKey as non-empty-string Proxy address
     * @template TValue as non-empty-string Trusted header
     */
    public function __construct(public array $value)
    {
    }
}
