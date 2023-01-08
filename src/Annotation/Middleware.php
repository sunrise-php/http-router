<?php declare(strict_types=1);

/**
 * It's free open-source software released under the MIT License.
 *
 * @author Anatoly Nekhay <afenric@gmail.com>
 * @copyright Copyright (c) 2018, Anatoly Nekhay
 * @license https://github.com/sunrise-php/http-router/blob/master/LICENSE
 * @link https://github.com/sunrise-php/http-router
 */

namespace Sunrise\Http\Router\Annotation;

/**
 * Import classes
 */
use Attribute;
use Psr\Http\Server\MiddlewareInterface;

/**
 * @Annotation
 *
 * @Target({"CLASS", "METHOD"})
 *
 * @NamedArgumentConstructor
 *
 * @Attributes({
 *   @Attribute("value", type="string", required=true),
 * })
 *
 * @since 2.11.0
 */
#[Attribute(Attribute::TARGET_CLASS|Attribute::TARGET_METHOD|Attribute::IS_REPEATABLE)]
final class Middleware
{

    /**
     * The attribute value
     *
     * @var class-string<MiddlewareInterface>
     */
    public string $value;

    /**
     * Constructor of the class
     *
     * @param class-string<MiddlewareInterface> $value
     */
    public function __construct(string $value)
    {
        $this->value = $value;
    }
}
