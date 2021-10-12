<?php declare(strict_types=1);

/**
 * It's free open-source software released under the MIT License.
 *
 * @author Anatoly Fenric <anatoly@fenric.ru>
 * @copyright Copyright (c) 2018, Anatoly Fenric
 * @license https://github.com/sunrise-php/http-router/blob/master/LICENSE
 * @link https://github.com/sunrise-php/http-router
 */

namespace Sunrise\Http\Router\Annotation;

/**
 * Import classes
 */
use Attribute;

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
#[Attribute(Attribute::TARGET_CLASS|Attribute::TARGET_METHOD)]
final class Postfix
{

    /**
     * The attribute value
     *
     * @var string
     */
    public $value;

    /**
     * Constructor of the class
     *
     * @param string $value
     */
    public function __construct(string $value)
    {
        $this->value = $value;
    }
}
