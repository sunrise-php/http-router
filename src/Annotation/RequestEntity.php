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

/**
 * @since 3.0.0
 */
#[Attribute(Attribute::TARGET_PARAMETER)]
final class RequestEntity
{

    /**
     * Constructor of the class
     *
     * @param string|null $em
     * @param string $findBy
     * @param string $paramKey
     * @param array<string, mixed> $criteria
     */
    public function __construct(
        public ?string $em = null,
        public string $findBy = 'id',
        public string $paramKey = 'id',
        public array $criteria = []
    ) {
    }
}
