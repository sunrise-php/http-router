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

namespace Sunrise\Http\Router\Validation\Constraint;

use ReflectionParameter;
use Symfony\Component\Validator\Constraint;

/**
 * @since 3.0.0
 *
 * @psalm-suppress PropertyNotSetInConstructor {@see parent::$groups}
 */
final class ArgumentConstraint extends Constraint
{
    public function __construct(
        private readonly ReflectionParameter $parameter,
    ) {
        parent::__construct();
    }

    public function getParameter(): ReflectionParameter
    {
        return $this->parameter;
    }
}
