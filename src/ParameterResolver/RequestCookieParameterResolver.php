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

namespace Sunrise\Http\Router\ParameterResolver;

use Generator;
use LogicException;
use Psr\Http\Message\ServerRequestInterface;
use ReflectionAttribute;
use ReflectionParameter;
use Sunrise\Http\Router\Annotation\RequestCookie;
use Sunrise\Http\Router\Exception\HttpException;
use Sunrise\Http\Router\Helper\HydratorHelper;
use Sunrise\Http\Router\Helper\ValidatorHelper;
use Sunrise\Http\Router\ServerRequest;
use Sunrise\Hydrator\Exception\InvalidDataException;
use Sunrise\Hydrator\Exception\InvalidValueException;
use Sunrise\Hydrator\HydratorInterface;
use Sunrise\Hydrator\Type;
use Symfony\Component\Validator\Validator\ValidatorInterface;

use function count;

/**
 * @since 3.0.0
 */
final class RequestCookieParameterResolver implements ParameterResolverInterface
{
    public function __construct(
        private readonly HydratorInterface $hydrator,
        private readonly ?ValidatorInterface $validator = null,
    ) {
    }

    /**
     * @inheritDoc
     *
     * @throws LogicException If the resolver is used incorrectly.
     *
     * @throws HttpException If a cookie was missed or invalid.
     */
    public function resolveParameter(ReflectionParameter $parameter, mixed $context): Generator
    {
        /** @var list<ReflectionAttribute<RequestCookie>> $annotations */
        $annotations = $parameter->getAttributes(RequestCookie::class);
        if ($annotations === []) {
            return;
        }

        if (! $context instanceof ServerRequestInterface) {
            throw new LogicException(
                'At this level of the application, any operations with the request are not possible.',
            );
        }

        $request = ServerRequest::create($context);
        $requestCookie = $annotations[0]->newInstance();

        $placeholders = [
            '{{ cookie_name }}' => $requestCookie->name,
        ];

        if (!$request->hasCookieParam($requestCookie->name)) {
            if ($parameter->isDefaultValueAvailable()) {
                return yield $parameter->getDefaultValue();
            } elseif ($parameter->allowsNull()) {
                return yield;
            }

            throw HttpException::cookieMissed($requestCookie->errorStatusCode, $requestCookie->errorMessage, $placeholders);
        }

        try {
            $argument = $this->hydrator->castValue($request->getCookieParam($requestCookie->name), Type::fromParameter($parameter), path: [$requestCookie->name]);
        } catch (InvalidDataException|InvalidValueException $e) {
            throw HttpException::cookieInvalid($requestCookie->errorStatusCode, $requestCookie->errorMessage, $placeholders, previous: $e)
                ->addConstraintViolation(...HydratorHelper::adaptConstraintViolations($e));
        }

        if (isset($this->validator)) {
            if (count($constraints = ValidatorHelper::getParameterConstraints($parameter)) > 0) {
                if (count($violations = $this->validator->validate($argument, $constraints)) > 0) {
                    throw HttpException::cookieInvalid($requestCookie->errorStatusCode, $requestCookie->errorMessage, $placeholders)
                        ->addConstraintViolation(...ValidatorHelper::adaptConstraintViolations(...$violations));
                }
            }
        }

        yield $argument;
    }
}
