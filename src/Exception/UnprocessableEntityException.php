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

namespace Sunrise\Http\Router\Exception;

use Sunrise\Http\Router\Exception\Http\HttpUnprocessableEntityException;
use Symfony\Component\Validator\ConstraintViolationListInterface;

/**
 * UnprocessableEntityException
 *
 * @since 3.0.0
 */
class UnprocessableEntityException extends HttpUnprocessableEntityException
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
        parent::__construct();

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