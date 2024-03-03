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
use ReflectionNamedType;
use ReflectionParameter;
use Sunrise\Http\Router\Annotation\RequestBody;
use Sunrise\Http\Router\Exception\HttpException;
use Sunrise\Http\Router\Helper\HydratorHelper;
use Sunrise\Http\Router\Helper\ValidatorHelper;
use Sunrise\Http\Router\ParameterResolver;
use Sunrise\Hydrator\Exception\InvalidDataException;
use Sunrise\Hydrator\HydratorInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

use function count;
use function sprintf;

/**
 * @since 3.0.0
 */
final class RequestBodyParameterResolver implements ParameterResolverInterface
{
    public function __construct(
        private readonly HydratorInterface $hydrator,
        private readonly ?ValidatorInterface $validator = null,
    ) {
    }

    /**
     * @inheritDoc
     *
     * @throws LogicException If the resolver is used incorrectly or if an object isn't valid.
     *
     * @throws HttpException If the request's parsed body isn't valid.
     */
    public function resolveParameter(ReflectionParameter $parameter, mixed $context): Generator
    {
        /** @var list<ReflectionAttribute<RequestBody>> $annotations */
        $annotations = $parameter->getAttributes(RequestBody::class);
        if ($annotations === []) {
            return;
        }

        $type = $parameter->getType();
        if (! $type instanceof ReflectionNamedType || $type->isBuiltin()) {
            throw new LogicException(sprintf(
                'To use the #[RequestBody] annotation, the parameter %s must be typed with an object.',
                ParameterResolver::stringifyParameter($parameter),
            ));
        }

        if (! $context instanceof ServerRequestInterface) {
            throw new LogicException(
                'At this level of the application, any operations with the request are not possible.'
            );
        }

        $requestBody = $annotations[0]->newInstance();

        try {
            $argument = $this->hydrator->hydrate($type->getName(), (array) $context->getParsedBody());
        } catch (InvalidDataException $e) {
            throw HttpException::bodyInvalid($requestBody->errorStatusCode, $requestBody->errorMessage, previous: $e)
                ->addConstraintViolation(...HydratorHelper::adaptConstraintViolations($e));
        }

        if (isset($this->validator)) {
            if (count($violations = $this->validator->validate($argument)) > 0) {
                throw HttpException::bodyInvalid($requestBody->errorStatusCode, $requestBody->errorMessage)
                    ->addConstraintViolation(...ValidatorHelper::adaptConstraintViolations(...$violations));
            }
        }

        yield $argument;
    }
}
