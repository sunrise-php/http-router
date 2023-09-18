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

namespace Sunrise\Http\Router\ParameterResolving\ParameterResolver;

use Generator;
use Psr\Http\Message\ServerRequestInterface;
use ReflectionAttribute;
use ReflectionParameter;
use Sunrise\Http\Router\Annotation\RequestCookie;
use Sunrise\Http\Router\Dictionary\ErrorSource;
use Sunrise\Http\Router\Exception\Http\HttpBadRequestException;
use Sunrise\Http\Router\Exception\LogicException;
use Sunrise\Http\Router\ParameterResolving\ParameterResolutioner;
use Sunrise\Http\Router\TypeConversion\TypeConversionerInterface;
use UnexpectedValueException;

use function sprintf;

/**
 * RequestCookieParameterResolver
 *
 * @since 3.0.0
 */
final class RequestCookieParameterResolver implements ParameterResolverInterface
{

    /**
     * Constructor of the class
     *
     * @param TypeConversionerInterface $typeConversioner
     */
    public function __construct(private TypeConversionerInterface $typeConversioner)
    {
    }

    /**
     * @inheritDoc
     *
     * @throws LogicException If the resolver is used incorrectly.
     *
     * @throws HttpBadRequestException If a cookie was missed or invalid.
     */
    public function resolveParameter(ReflectionParameter $parameter, mixed $context): Generator
    {
        /** @var list<ReflectionAttribute<RequestCookie>> $attributes */
        $attributes = $parameter->getAttributes(RequestCookie::class);
        if ($attributes === []) {
            return;
        }

        if (!$parameter->hasType()) {
            throw new LogicException(sprintf(
                'To use the #[RequestCookie] attribute, the parameter {%s} must be typed.',
                ParameterResolutioner::stringifyParameter($parameter),
            ));
        }

        if (! $context instanceof ServerRequestInterface) {
            throw new LogicException(
                'At this level of the application, any operations with the request are not possible.'
            );
        }

        $cookie = $attributes[0]->newInstance();
        $cookies = $context->getCookieParams();

        if (!isset($cookies[$cookie->name])) {
            if ($parameter->isDefaultValueAvailable()) {
                return yield $parameter->getDefaultValue();
            } elseif ($parameter->allowsNull()) {
                return yield;
            }

            $message = sprintf('The cookie %s must be provided.', $cookie->name);

            throw (new HttpBadRequestException($message))
                ->setSource(ErrorSource::CLIENT_REQUEST_COOKIE);
        }

        try {
            yield $this->typeConversioner->castValue($cookies[$cookie->name], $parameter->getType());
        } catch (UnexpectedValueException $violation) {
            $message = sprintf('The value of the cookie %s is not valid. %s', $cookie->name, $violation->getMessage());

            throw (new HttpBadRequestException($message, previous: $violation))
                ->setSource(ErrorSource::CLIENT_REQUEST_COOKIE);
        }
    }
}
