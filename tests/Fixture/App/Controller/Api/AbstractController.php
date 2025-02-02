<?php

declare(strict_types=1);

namespace Sunrise\Http\Router\Tests\Fixture\App\Controller\Api;

use Sunrise\Http\Router\Annotation\NamePrefix;
use Sunrise\Http\Router\Annotation\PathPrefix;
use Sunrise\Http\Router\Tests\Fixture\App\Controller\AbstractController as BaseAbstractController;

#[PathPrefix('/api')]
#[NamePrefix('api.')]
abstract class AbstractController extends BaseAbstractController
{
}
