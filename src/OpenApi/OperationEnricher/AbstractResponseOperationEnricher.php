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

namespace Sunrise\Http\Router\OpenApi\OperationEnricher;

use ReflectionAttribute;
use ReflectionClass;
use ReflectionMethod;
use Sunrise\Http\Router\Annotation\ResponseHeader;
use Sunrise\Http\Router\Annotation\ResponseStatus;
use Sunrise\Http\Router\OpenApi\Type;

/**
 * @since 3.0.0
 */
abstract class AbstractResponseOperationEnricher
{
    /**
     * @param ReflectionClass<object>|ReflectionMethod $requestHandler
     */
    final protected static function getResponseStatusCode(
        ReflectionClass|ReflectionMethod $requestHandler,
    ): ?int {
        if ($requestHandler instanceof ReflectionMethod) {
            /** @var list<ReflectionAttribute<ResponseStatus>> $annotations */
            $annotations = $requestHandler->getAttributes(ResponseStatus::class);
            if (isset($annotations[0])) {
                $responseStatus = $annotations[0]->newInstance();

                return $responseStatus->code;
            }
        }

        return null;
    }

    /**
     * @param ReflectionClass<object>|ReflectionMethod $requestHandler
     * @param array<array-key, mixed> $response
     * @param-out array<array-key, mixed> $response
     */
    final protected static function enrichResponseWithHeaders(
        ReflectionClass|ReflectionMethod $requestHandler,
        array &$response,
    ): void {
        if ($requestHandler instanceof ReflectionMethod) {
            /** @var list<ReflectionAttribute<ResponseHeader>> $annotations */
            $annotations = $requestHandler->getAttributes(ResponseHeader::class);
            foreach ($annotations as $annotation) {
                $responseHeader = $annotation->newInstance();

                $response['headers'][$responseHeader->name] = [
                    'schema' => [
                        'type' => Type::OAS_TYPE_NAME_STRING,
                        'example' => $responseHeader->value,
                    ],
                ];
            }
        }
    }
}
