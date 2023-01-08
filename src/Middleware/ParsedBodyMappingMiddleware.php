<?php declare(strict_types=1);

/**
 * It's free open-source software released under the MIT License.
 *
 * @author Anatoly Nekhay <afenric@gmail.com>
 * @copyright Copyright (c) 2018, Anatoly Nekhay
 * @license https://github.com/sunrise-php/http-router/blob/master/LICENSE
 * @link https://github.com/sunrise-php/http-router
 */

namespace Sunrise\Http\Router\Middleware;

/**
 * Import classes
 */
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Sunrise\Http\Router\Annotation\Body;
use Sunrise\Http\Router\Exception\Http\HttpUnprocessableEntityException;
use Sunrise\Http\Router\Exception\LogicException;
use Sunrise\Http\Router\RequestHandler\CallableRequestHandler;
use Sunrise\Http\Router\RouteInterface;
use Sunrise\Hydrator\Exception\InvalidValueException;
use Sunrise\Hydrator\Exception\InvalidObjectException;
use Sunrise\Hydrator\HydratorInterface;
use Sunrise\Hydrator\Hydrator;
use ReflectionNamedType;

/**
 * Import functions
 */
use function class_exists;
use function sprintf;

/**
 * Import constants
 */
use const PHP_MAJOR_VERSION;

/**
 * ParsedBodyMappingMiddleware
 *
 * @link https://github.com/sunrise-php/hydrator
 *
 * @since 3.0.0
 */
class ParsedBodyMappingMiddleware implements MiddlewareInterface
{

    /**
     * Objects hydrator
     *
     * @var HydratorInterface
     */
    private HydratorInterface $objectHydrator;

    /**
     * Constructor of the class
     *
     * @param HydratorInterface|null $objectHydrator
     *
     * @throws LogicException
     *         If the PHP version less than 8.
     */
    public function __construct(?HydratorInterface $objectHydrator = null)
    {
        if (PHP_MAJOR_VERSION < 8) {
            throw new LogicException('Parsed body mapping requires PHP version 8');
        }

        $this->objectHydrator = $objectHydrator ?? new Hydrator();
    }

    /**
     * {@inheritdoc}
     *
     * @throws LogicException
     *         If something went wrong...
     *
     * @throws HttpUnprocessableEntityException
     *         If the request body isn't valid.
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $route = $request->getAttribute(RouteInterface::ATTR_ROUTE);
        if (!($route instanceof RouteInterface)) {
            throw new LogicException(sprintf(
                'The "%s" middleware must be related with a route',
                __CLASS__
            ));
        }

        $routeRequestHandler = $route->getRequestHandler();
        if (!($routeRequestHandler instanceof CallableRequestHandler)) {
            throw new LogicException(sprintf(
                'The "%s" route cannot be related with the "%s" middleware',
                $route->getName(),
                __CLASS__
            ));
        }

        $routeRequestHandlerProcessableParameter = $routeRequestHandler->getAttributedParameter(Body::class);
        if (!isset($routeRequestHandlerProcessableParameter)) {
            return $handler->handle($request);
        }

        $routeRequestHandlerProcessableParameterType = $routeRequestHandlerProcessableParameter->getType();
        if (!($routeRequestHandlerProcessableParameterType instanceof ReflectionNamedType)) {
            throw new LogicException(sprintf(
                'The "%s" parameter of the "%s" route request handler cannot be hydrated ' .
                'because its type is not supported, do not use union or intersection types',
                $routeRequestHandlerProcessableParameter->getName(),
                $route->getName()
            ));
        }

        $routeRequestHandlerProcessableParameterClassName = $routeRequestHandlerProcessableParameterType->getName();
        if (!class_exists($routeRequestHandlerProcessableParameterClassName)) {
            throw new LogicException(sprintf(
                'The "%s" parameter of the "%s" route request handler cannot be hydrated ' .
                'because its refers to the "%s" class that cannot be found',
                $routeRequestHandlerProcessableParameter->getName(),
                $route->getName(),
                $routeRequestHandlerProcessableParameterType->getName()
            ));
        }

        try {
            $hydratedObject = $this->objectHydrator->hydrate(
                $routeRequestHandlerProcessableParameterClassName,
                (array) $request->getParsedBody()
            );
        } catch (InvalidObjectException $e) {
            throw new LogicException(sprintf(
                'The "%s" parameter of the "%s" route request handler cannot be hydrated ' .
                'because its refers to an invalid DTO: %s',
                $routeRequestHandlerProcessableParameter->getName(),
                $route->getName(),
                $e->getMessage()
            ), 0, $e);
        } catch (InvalidValueException $e) {
            throw new HttpUnprocessableEntityException($e->getMessage(), 0, $e);
        }

        $routeRequestHandlerArguments = $routeRequestHandler->getArguments();
        $routeRequestHandlerArguments[$routeRequestHandlerProcessableParameter->getPosition()] = $hydratedObject;
        $route->setRequestHandler($routeRequestHandler->withArguments($routeRequestHandlerArguments));

        return $handler->handle($request);
    }
}
