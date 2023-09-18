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
use Sunrise\Http\Router\Annotation\RequestHeader;
use Sunrise\Http\Router\Dictionary\ErrorSource;
use Sunrise\Http\Router\Exception\Http\HttpBadRequestException;
use Sunrise\Http\Router\Exception\LogicException;
use Sunrise\Http\Router\ParameterResolving\ParameterResolutioner;
use Sunrise\Http\Router\TypeConversion\TypeConversionerInterface;
use UnexpectedValueException;

use function sprintf;

/**
 * RequestHeaderParameterResolver
 *
 * @since 3.0.0
 */
final class RequestHeaderParameterResolver implements ParameterResolverInterface
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
     * @throws HttpBadRequestException If a header was missed or invalid.
     */
    public function resolveParameter(ReflectionParameter $parameter, mixed $context): Generator
    {
        /** @var list<ReflectionAttribute<RequestHeader>> $attributes */
        $attributes = $parameter->getAttributes(RequestHeader::class);
        if ($attributes === []) {
            return;
        }

        if (!$parameter->hasType()) {
            throw new LogicException(sprintf(
                'To use the #[RequestHeader] attribute, the parameter {%s} must be typed.',
                ParameterResolutioner::stringifyParameter($parameter),
            ));
        }

        if (! $context instanceof ServerRequestInterface) {
            throw new LogicException(
                'At this level of the application, any operations with the request are not possible.'
            );
        }

        $header = $attributes[0]->newInstance();

        if (!$context->hasHeader($header->name)) {
            if ($parameter->isDefaultValueAvailable()) {
                return yield $parameter->getDefaultValue();
            } elseif ($parameter->allowsNull()) {
                return yield;
            }

            $message = sprintf('The HTTP header %s must be provided.', $header->name);

            throw (new HttpBadRequestException($message))
                ->setSource(ErrorSource::CLIENT_REQUEST_HEADER);
        }

        try {
            yield $this->typeConversioner->castValue($context->getHeaderLine($header->name), $parameter->getType());
        } catch (UnexpectedValueException $violation) {
            // phpcs:ignore Generic.Files.LineLength
            $message = sprintf('The value of the HTTP header %s is not valid. %s', $header->name, $violation->getMessage());

            throw (new HttpBadRequestException($message, previous: $violation))
                ->setSource(ErrorSource::CLIENT_REQUEST_HEADER);
        }
    }
}
