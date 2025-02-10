<?php

declare(strict_types=1);

namespace Sunrise\Http\Router\Tests\Fixture\App\Controller\Api;

use Sunrise\Http\Router\Annotation\NamePrefix;
use Sunrise\Http\Router\Annotation\PathPrefix;
use Sunrise\Http\Router\OpenApi\Annotation\Operation;
use Sunrise\Http\Router\Tests\Fixture\App\Controller\AbstractController as BaseAbstractController;

#[PathPrefix('/api')]
#[NamePrefix('api.')]
#[Operation([
    'externalDocs' => [
        'description' => 'Find more info here',
        'url' => 'https://example.com',
    ],
])]
abstract class AbstractController extends BaseAbstractController
{
}
