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
use Fig\Http\Message\RequestMethodInterface;

/**
 * Pay attention to the {@see RequestMethodInterface} dictionary.
 *
 * @link https://dev.sunrise-studio.io/docs/reference/router-annotations?id=method
 * @since 3.0.0
 */
#[Attribute(Attribute::TARGET_CLASS | Attribute::TARGET_METHOD | Attribute::IS_REPEATABLE)]
class Method implements RequestMethodInterface
{
    /**
     * @var array<array-key, string>
     */
    public readonly array $values;

    public function __construct(string ...$values)
    {
        $this->values = $values;
    }
}
