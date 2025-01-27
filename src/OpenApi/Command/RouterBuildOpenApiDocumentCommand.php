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

namespace Sunrise\Http\Router\OpenApi\Command;

use Sunrise\Http\Router\OpenApi\OpenApiDocumentManagerInterface;
use Sunrise\Http\Router\RouteInterface;
use Sunrise\Http\Router\RouterInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

use function array_filter;

/**
 * @since 3.0.0
 */
#[AsCommand('router:build-openapi-document', 'Builds the OpenAPI document.')]
final class RouterBuildOpenApiDocumentCommand extends Command
{
    public function __construct(
        private readonly OpenApiDocumentManagerInterface $openApiDocumentManager,
        private readonly RouterInterface $router,
    ) {
        parent::__construct();
    }

    /**
     * @inheritDoc
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->openApiDocumentManager->saveDocument(
            $this->openApiDocumentManager->buildDocument(
                array_filter(
                    $this->router->getRoutes(),
                    static function (RouteInterface $route): bool {
                        return $route->isApiRoute();
                    },
                ),
            )
        );

        $output->writeln('Done.');

        return self::SUCCESS;
    }
}
