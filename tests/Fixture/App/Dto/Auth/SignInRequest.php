<?php

declare(strict_types=1);

namespace Sunrise\Http\Router\Tests\Fixture\App\Dto\Auth;

use SensitiveParameter;

final class SignInRequest
{
    public function __construct(
        #[SensitiveParameter]
        public readonly string $email,
        #[SensitiveParameter]
        public readonly string $password,
    ) {
    }
}
