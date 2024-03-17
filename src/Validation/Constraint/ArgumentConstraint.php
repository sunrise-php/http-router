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
 * @psalm-suppress PropertyNotSetInConstructor https://github.com/symfony/validator/blob/33e1f3bb76ef70e3170e12f878aefb9c69b0fc4c/Constraint.php#L71
 */
final class ArgumentConstraint extends Constraint
{
    public function __construct(private readonly ReflectionParameter $parameter)
    {
        parent::__construct();
    }

    public function getParameter(): ReflectionParameter
    {
        return $this->parameter;
    }
}
