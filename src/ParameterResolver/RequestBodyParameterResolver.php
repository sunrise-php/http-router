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
use Psr\Http\Message\ServerRequestInterface;
use ReflectionNamedType;
use ReflectionParameter;
use Sunrise\Http\Router\Annotation\RequestBody;
use Sunrise\Http\Router\Exception\LogicException;
use Sunrise\Http\Router\Exception\UnhydrableObjectException;
use Sunrise\Http\Router\Exception\UnprocessableRequestBodyException;
use Sunrise\Http\Router\ParameterResolutioner;
use Sunrise\Hydrator\Exception\InvalidDataException;
use Sunrise\Hydrator\Exception\InvalidObjectException;
use Sunrise\Hydrator\HydratorInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

use function sprintf;

/**
 * RequestBodyParameterResolver
 *
 * @link https://github.com/sunrise-php/hydrator
 * @link https://github.com/symfony/validator
 *
 * @since 3.0.0
 */
final class RequestBodyParameterResolver implements ParameterResolverInterface
{

    /**
     * Constructor of the class
     *
     * @param HydratorInterface $hydrator
     * @param ValidatorInterface $validator
     */
    public function __construct(private HydratorInterface $hydrator, private ValidatorInterface $validator)
    {
    }

    /**
     * @inheritDoc
     *
     * @throws UnhydrableObjectException If an object isn't valid.
     * @throws UnprocessableRequestBodyException If the request's parsed body isn't valid.
     * @throws LogicException If the resolver is used incorrectly.
     */
    public function resolveParameter(ReflectionParameter $parameter, mixed $context): Generator
    {
        if ($parameter->getAttributes(RequestBody::class) === []) {
            return;
        }

        $type = $parameter->getType();

        if (! $type instanceof ReflectionNamedType || $type->isBuiltin()) {
            throw new LogicException(sprintf(
                'To use the #[RequestBody] attribute, the parameter {%s} must be typed with an object.',
                ParameterResolutioner::stringifyParameter($parameter),
            ));
        }

        if (! $context instanceof ServerRequestInterface) {
            throw new LogicException(
                'At this level of the application, any operations with the request are not possible.'
            );
        }

        try {
            $object = $this->hydrator->hydrate($type->getName(), (array) $context->getParsedBody());
        } catch (InvalidObjectException $e) {
            throw new UnhydrableObjectException($e->getMessage(), 0, $e);
        } catch (InvalidDataException $e) {
            throw new UnprocessableRequestBodyException($e->getViolations());
        }

        $violations = $this->validator->validate($object);
        if ($violations->count() > 0) {
            throw new UnprocessableRequestBodyException($violations);
        }

        yield $object;
    }
}
