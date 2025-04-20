<?php

declare(strict_types=1);

namespace Sunrise\Http\Router\Tests\Fixture\App\Controller\Api;

use Sunrise\Http\Router\Annotation\Consumes;
use Sunrise\Http\Router\Annotation\PostApiRoute;
use Sunrise\Http\Router\Annotation\RequestBody;
use Sunrise\Http\Router\Dictionary\MediaType;
use Sunrise\Http\Router\Tests\Fixture\App\Dto\Auth\SignInRequest;

final class AuthController extends AbstractController
{
    #[PostApiRoute('signIn', '/sign-in')]
    #[Consumes(MediaType::JSON)]
    public function signIn(#[RequestBody] SignInRequest $signInRequest): void
    {
    }
}
