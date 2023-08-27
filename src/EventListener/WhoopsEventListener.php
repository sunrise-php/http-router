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

namespace Sunrise\Http\Router\EventListener;

use Psr\Http\Message\ResponseFactoryInterface;
use Sunrise\Http\Router\Entity\MediaType;
use Sunrise\Http\Router\Event\ErrorEvent;
use Sunrise\Http\Router\Exception\Http\HttpInternalServerErrorException;
use Sunrise\Http\Router\Exception\LogicException;
use Sunrise\Http\Router\ServerRequest;
use Whoops\Handler\JsonResponseHandler;
use Whoops\Handler\PrettyPageHandler;
use Whoops\Handler\XmlResponseHandler;
use Whoops\Run as Whoops;

use function class_exists;

/**
 * Renders fatal errors using the Whoops package
 *
 * @link https://github.com/filp/whoops
 *
 * @since 3.0.0
 */
final class WhoopsEventListener
{

    /**
     * Constructor of the class
     *
     * @param ResponseFactoryInterface $responseFactory
     *
     * @throws LogicException If the filp/whoops package wasn't installed.
     */
    public function __construct(private ResponseFactoryInterface $responseFactory)
    {
        if (!class_exists(Whoops::class)) {
            throw new LogicException(
                'The filp/whoops package is required, run the `composer require filp/whoops` to resolve it.'
            );
        }
    }

    /**
     * Handles the given event
     *
     * @param ErrorEvent $event
     *
     * @return ErrorEvent
     */
    public function __invoke(ErrorEvent $event): ErrorEvent
    {
        $error = $event->getError();

        if (! $error instanceof HttpInternalServerErrorException) {
            return $event;
        }

        $clientPreferredMediaType = ServerRequest::from($event->getRequest())
            ->getClientPreferredMediaType(
                MediaType::html(),
                MediaType::json(),
                MediaType::xml(),
            );

        $whoops = new Whoops();
        $whoops->allowQuit(false);
        $whoops->writeToOutput(false);

        if ($clientPreferredMediaType->equals(MediaType::html())) {
            $whoops->pushHandler(new PrettyPageHandler);
        } elseif ($clientPreferredMediaType->equals(MediaType::json())) {
            $whoops->pushHandler(new JsonResponseHandler());
        } elseif ($clientPreferredMediaType->equals(MediaType::xml())) {
            $whoops->pushHandler(new XmlResponseHandler());
        }

        $response = $this->responseFactory->createResponse(500)
            ->withHeader('Content-Type', $clientPreferredMediaType->__toString());

        $response->getBody()->write($whoops->handleException($error->getError()));

        $event->setResponse($response);
        $event->stopPropagation();

        return $event;
    }
}
