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
use Sunrise\Http\Router\Entity\MediaType\MediaTypeFactory;

/**
 * @since 3.0.0
 */
#[Attribute(Attribute::TARGET_CLASS | Attribute::TARGET_METHOD | Attribute::IS_REPEATABLE)]
final class ProducesHtml extends Produces
{
    public function __construct()
    {
        parent::__construct(MediaTypeFactory::html());
    }
}