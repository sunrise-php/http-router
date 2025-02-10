<?php

declare(strict_types=1);

namespace Sunrise\Http\Router\Tests\Fixture\App\Controller\Api;

use Sunrise\Http\Router\Annotation\NamePrefix;
use Sunrise\Http\Router\Annotation\PathPrefix;
use Sunrise\Http\Router\OpenApi\Annotation\Operation;
use Sunrise\Http\Router\OpenApi\Type;
use Sunrise\Http\Router\Tests\Fixture\App\Controller\AbstractController as BaseAbstractController;

#[PathPrefix('/api')]
#[NamePrefix('api.')]
#[Operation([
    // this is bad, just for testing.
    'parameters' => [
        [
            'in' => 'header',
            'name' => 'X-Request-ID',
            'schema' => new Type(Type::OAS_TYPE_NAME_STRING),
        ],
    ],
])]
abstract class AbstractController extends BaseAbstractController
{
}
