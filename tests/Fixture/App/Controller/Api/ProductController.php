<?php

declare(strict_types=1);

namespace Sunrise\Http\Router\Tests\Fixture\App\Controller\Api;

use Psr\Http\Message\StreamInterface;
use SensitiveParameter;
use Sunrise\Http\Router\Annotation\Consumes;
use Sunrise\Http\Router\Annotation\Deprecated;
use Sunrise\Http\Router\Annotation\NamePrefix;
use Sunrise\Http\Router\Annotation\PathPrefix;
use Sunrise\Http\Router\Annotation\PostApiRoute;
use Sunrise\Http\Router\Annotation\PutApiRoute;
use Sunrise\Http\Router\Annotation\RequestBody;
use Sunrise\Http\Router\Annotation\RequestCookie;
use Sunrise\Http\Router\Annotation\RequestHeader;
use Sunrise\Http\Router\Annotation\RequestVariable;
use Sunrise\Http\Router\Annotation\ResponseHeader;
use Sunrise\Http\Router\Annotation\Summary;
use Sunrise\Http\Router\Annotation\Tag;
use Sunrise\Http\Router\Dictionary\MediaType;
use Sunrise\Http\Router\Tests\Fixture\App\Dto\Product\ProductCreateRequest;
use Symfony\Component\Uid\UuidV4;

#[PathPrefix('/products')]
#[NamePrefix('products.')]
#[Tag('Products')]
final class ProductController extends AbstractController
{
    #[PostApiRoute('create')]
    #[Consumes(MediaType::JSON)]
    #[Summary('Creates a new product')]
    public function createProduct(
        #[RequestBody]
        ProductCreateRequest $createRequest,
    ): void {
    }

    #[PutApiRoute('uploadPhoto', '/{id}/photo')]
    #[Consumes(\Sunrise\Http\Router\Tests\Fixture\App\Dictionary\MediaType::Jpeg)]
    #[Consumes(\Sunrise\Http\Router\Tests\Fixture\App\Dictionary\MediaType::Png)]
    #[Summary('Uploads a new product photo')]
    #[Deprecated]
    #[ResponseHeader('X-Foo', 'foo')]
    #[ResponseHeader('X-Bar', 'bar')]
    public function uploadProductPhoto(
        #[RequestHeader('Authorization')]
        #[SensitiveParameter]
        string $token,
        #[RequestCookie('csrf_token')]
        #[SensitiveParameter]
        string $csrfToken,
        #[RequestVariable(name: 'id')]
        UuidV4 $productId,
        StreamInterface $stream,
    ): void {
    }
}
