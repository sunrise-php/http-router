<?php declare(strict_types=1);

/**
 * It's free open-source software released under the MIT License.
 *
 * @author Anatoly Nekhay <afenric@gmail.com>
 * @copyright Copyright (c) 2018, Anatoly Nekhay
 * @license https://github.com/sunrise-php/http-router/blob/master/LICENSE
 * @link https://github.com/sunrise-php/http-router
 */

namespace Sunrise\Http\Router\Exception;

/**
 * Import classes
 */
use Sunrise\Http\Router\Exception\Http\HttpUnprocessableEntityException;
use Symfony\Component\Validator\ConstraintViolationListInterface;

/**
 * UnprocessableRequestEntityException
 *
 * @since 3.0.0
 */
class UnprocessableRequestEntityException extends HttpUnprocessableEntityException
{

    /**
     * @var ConstraintViolationListInterface
     */
    private ConstraintViolationListInterface $violations;

    /**
     * Constructor of the class
     *
     * @param ConstraintViolationListInterface $violations
     */
    public function __construct(ConstraintViolationListInterface $violations)
    {
        $this->violations = $violations;
    }

    /**
     * Gets the violations list
     *
     * @return ConstraintViolationListInterface
     */
    final public function getViolations(): ConstraintViolationListInterface
    {
        return $this->violations;
    }
}
