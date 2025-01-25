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

use ReflectionAttribute;
use ReflectionMethod;
use Sunrise\Http\Router\Annotation\EncodableResponse;
use Sunrise\Http\Router\Annotation\RequestCookie;
use Sunrise\Http\Router\Annotation\RequestHeader;
use Sunrise\Http\Router\Annotation\ResponseHeader;
use Sunrise\Http\Router\Annotation\ResponseStatus;
use Sunrise\Http\Router\Helper\RouteBuilder;
use Sunrise\Http\Router\Helper\RouteParser;
use Sunrise\Http\Router\OpenApi\OpenApiDocumentManagerInterface;
use Sunrise\Http\Router\OpenApi\Type;
use Sunrise\Http\Router\OpenApi\TypeFactory;
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
#[AsCommand('router:build-oas-doc', 'Builds the OpenAPI document.')]
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
                )
            )
        );

        $output->writeln('Done.');

        return self::SUCCESS;
    }

    private static function enrichOperationWithPathParameters(RouteInterface $route, array &$operation): void
    {
        $routeVars = RouteParser::parseRoute($route->getPath());
        $routePatterns = $route->getPatterns();
        $routeAttributes = $route->getAttributes();

        foreach ($routeVars as $routeVar) {
            $parameter = [];
            $parameter['in'] = 'path';
            $parameter['name'] = $routeVar['name'];
            $parameter['schema']['type'] = 'string';

            $pattern = $routeVar['pattern'] ?? $routePatterns[$routeVar['name']] ?? null;
            if ($pattern !== null) {
                $parameter['schema']['pattern'] = '^' . $pattern . '$';
            }

            if (isset($routeAttributes[$routeVar['name']])) {
                $default = RouteBuilder::stringifyValue($routeAttributes[$routeVar['name']]);
                $parameter['schema']['default'] = $default;
            }

            if (!isset($routeVar['optional_part'])) {
                $parameter['required'] = true;
            }

            $operation['parameters'][] = $parameter;
        }
    }

    private static function enrichOperationWithCookieAndHeaderParameters(
        ReflectionMethod $endpoint,
        array &$operation,
    ): void {
        foreach ($endpoint->getParameters() as $endpointParameter) {
            /** @var ReflectionAttribute $annotations */
            $annotations = array_merge(
                $endpointParameter->getAttributes(RequestCookie::class),
                $endpointParameter->getAttributes(RequestHeader::class),
            );

            foreach ($annotations as $annotation) {
                $annotation = $annotation->newInstance();

                $operationParameter = [];

                $operationParameter['in'] = match ($annotation::class) {
                    RequestCookie::class => 'cookie',
                    RequestHeader::class => 'header',
                };

                $operationParameter['name'] = $annotation->name;
                $operationParameter['schema']['type'] = 'string';

                if (!$endpointParameter->isDefaultValueAvailable()) {
                    $operationParameter['required'] = true;
                }

                $operation['parameters'][] = $operationParameter;
            }
        }
    }

    private function enrichOperationWithResponses(
        RouteInterface $route,
        ReflectionMethod $requestHandler,
        array &$operation,
    ): void {
        $responseStatusCode = $this->openApiConfiguration->defaultSuccessfulResponseStatusCode;
        $responseDescription = $this->openApiConfiguration->defaultSuccessfulResponseDescription;
        $responseSchema = [];

        if ($requestHandler->getReturnType()?->getName() === Type::PHP_TYPE_NAME_VOID) {
            $responseStatusCode = $this->openApiConfiguration->defaultEmptyResponseStatusCode;
        } elseif ($requestHandler->getAttributes(EncodableResponse::class) !== []) {
            $responseType = TypeFactory::fromPhpTypeReflection($requestHandler->getReturnType());
            $responseSchema = $this->phpTypeSchemaResolverChain->resolvePhpTypeSchema($responseType, $requestHandler);
        }

        /** @var list<ReflectionAttribute<ResponseStatus>> $annotations */
        $annotations = $requestHandler->getAttributes(ResponseStatus::class);
        if (isset($annotations[0])) {
            $annotation = $annotations[0]->newInstance();
            $responseStatusCode = $annotation->code;
        }

        /** @var list<ReflectionAttribute<ResponseSchema>> $annotations */
        $annotations = $requestHandler->getAttributes(ResponseSchema::class);
        if (isset($annotations[0])) {
            $annotation = $annotations[0]->newInstance();
            $responseSchema = $annotation->value;
        }

        $operation['responses'][$responseStatusCode]['description'] = $responseDescription;

        /** @var ReflectionAttribute<ResponseHeader> $annotation */
        foreach ($requestHandler->getAttributes(ResponseHeader::class) as $annotation) {
            $annotation = $annotation->newInstance();

            $operation['responses'][$responseStatusCode]['headers'][$annotation->name]['schema'] = [
                'type' => 'string',
            ];
        }

        foreach ($route->getProducedMediaTypes() as $mediaType) {
            // phpcs:disable Generic.Files.LineLength.TooLong
            $operation['responses'][$responseStatusCode]['content'][$mediaType->getIdentifier()]['schema'] = $responseSchema;
        }
    }
}
