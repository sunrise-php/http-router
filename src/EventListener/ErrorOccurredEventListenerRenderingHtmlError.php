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

use Sunrise\Http\Router\Dto\ErrorDto;
use Sunrise\Http\Router\Entity\MediaType;
use Sunrise\Http\Router\Event\ErrorOccurredEvent;
use Sunrise\Http\Router\Exception\HttpExceptionInterface;
use Sunrise\Http\Router\ServerRequest;
use Twig\Environment;
use Twig\Loader\FilesystemLoader;

/**
 * Renders errors using the Twig package
 *
 * @link https://github.com/twigphp/Twig
 *
 * @since 3.0.0
 */
final class ErrorOccurredEventListenerRenderingHtmlError
{

    /**
     * Constructor of the class
     *
     * @param Environment $twig
     */
    public function __construct(private Environment $twig)
    {
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
        if (! $error instanceof HttpExceptionInterface) {
            return $event;
        }

        $request = ServerRequest::from($event->getRequest());
        if (! $request->clientConsumesMediaType(MediaType::html())) {
            return $event;
        }

        $loader = $this->twig->getLoader();
        if ($loader instanceof FilesystemLoader) {
            $loader->addPath(__DIR__ . '/../../resources/templates');
        }

        $response = $event->getResponse()
            ->withHeader('Content-Type', 'text/html; charset=UTF-8');

        $response->getBody()->write(
            $this->twig->render('error.html', [
                'error' => ErrorDto::fromHttpError($error),
            ])
        );

        $event->setResponse($response);

        return $event;
    }
}
