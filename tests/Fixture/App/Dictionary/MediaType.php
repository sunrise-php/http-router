<?php

declare(strict_types=1);

namespace Sunrise\Http\Router\Tests\Fixture\App\Dictionary;

use Sunrise\Coder\MediaTypeInterface;

enum MediaType: string implements MediaTypeInterface
{
    case Jpeg = 'image/jpeg';
    case Png = 'image/png';

    public function getIdentifier(): string
    {
        return $this->value;
    }
}
