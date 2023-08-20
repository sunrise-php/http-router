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
final class RequestEntity
{

    /**
     * Constructor of the class
     *
     * @param non-empty-string|null $em
     * @param non-empty-string|null $findBy
     * @param non-empty-string|null $valueKey
     * @param array<non-empty-string, mixed> $criteria An entity's additional search criteria
     */
    public function __construct(
        public string|null $em = null,
        public string|null $findBy = null,
        public string|null $valueKey = null,
        public array $criteria = [],
    ) {
    }
}
