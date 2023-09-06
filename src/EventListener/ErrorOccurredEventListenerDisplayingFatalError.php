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

use Sunrise\Http\Router\Entity\MediaType;
use Sunrise\Http\Router\Event\ErrorOccurredEvent;
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
final class ErrorOccurredEventListenerDisplayingFatalError
{

    /**
     * Constructor of the class
     *
     * @throws LogicException If the filp/whoops package wasn't installed.
     */
    public function __construct()
    {
        if (!class_exists(Whoops::class)) {
            throw new LogicException(
                'The whoops package is required to display fatal errors, ' .
                'run the `composer require filp/whoops` to resolve it.'
            );
        }
    }

    /**
     * Handles the given event
     *
     * @param ErrorOccurredEvent $event
     *
     * @return ErrorOccurredEvent
     */
    public function __invoke(ErrorOccurredEvent $event): ErrorOccurredEvent
    {
        $error = $event->getError();
        if (! $error instanceof HttpInternalServerErrorException) {
            return $event;
        }

        $whoops = new Whoops();
        $whoops->allowQuit(false);
        $whoops->writeToOutput(false);

        $serverProducesMediaTypes = [MediaType::html(), MediaType::json(), MediaType::xml()];

        $clientPreferredMediaType = ServerRequest::from($event->getRequest())
            ->getClientPreferredMediaType(...$serverProducesMediaTypes);

        if ($clientPreferredMediaType === $serverProducesMediaTypes[0]) {
            $whoops->pushHandler(new PrettyPageHandler());
        } elseif ($clientPreferredMediaType === $serverProducesMediaTypes[1]) {
            $whoops->pushHandler(new JsonResponseHandler());
        } elseif ($clientPreferredMediaType === $serverProducesMediaTypes[2]) {
            $whoops->pushHandler(new XmlResponseHandler());
        }

        $response = $event->getResponse();
        $response = $response->withHeader('Content-Type', $clientPreferredMediaType->build(['charset' => 'UTF-8']));
        $response->getBody()->write($whoops->handleException($error->getError()));

        $event->setResponse($response);
        $event->stopPropagation();

        return $event;
    }
}
