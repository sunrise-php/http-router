<?php

declare(strict_types=1);

namespace Sunrise\Http\Router\Tests\Fixture\App\Controller\Api;

use Sunrise\Http\Router\Annotation\Consumes;
use Sunrise\Http\Router\Annotation\EncodableResponse;
use Sunrise\Http\Router\Annotation\NamePrefix;
use Sunrise\Http\Router\Annotation\PathPrefix;
use Sunrise\Http\Router\Annotation\PostApiRoute;
use Sunrise\Http\Router\Annotation\Produces;
use Sunrise\Http\Router\Annotation\PutApiRoute;
use Sunrise\Http\Router\Annotation\RequestBody;
use Sunrise\Http\Router\Annotation\ResponseStatus;
use Sunrise\Http\Router\Annotation\Summary;
use Sunrise\Http\Router\Annotation\Tag;
use Sunrise\Http\Router\Dictionary\MediaType;
use Sunrise\Http\Router\Tests\Fixture\App\Dto\PageCreateRequest;
use Sunrise\Http\Router\Tests\Fixture\App\Dto\PageUpdateRequest;
use Sunrise\Http\Router\Tests\Fixture\App\View\PageView;

#[PathPrefix('/pages')]
#[NamePrefix('pages.')]
#[Tag('Pages')]
final class PageController extends AbstractController
{
    #[PostApiRoute]
    #[Consumes(MediaType::JSON)]
    #[Produces(MediaType::JSON)]
    #[ResponseStatus(201)]
    #[EncodableResponse]
    #[Summary('Creates a new page')]
    public function create(
        #[RequestBody] PageCreateRequest $pageCreateRequest,
    ): PageView {
        return new PageView(
            name: $pageCreateRequest->name,
        );
    }

    #[PutApiRoute(path: '/{id}')]
    #[Consumes(MediaType::JSON)]
    #[Produces(MediaType::JSON)]
    #[EncodableResponse]
    #[Summary('Updates a page')]
    public function update(
        #[RequestBody] PageUpdateRequest $pageUpdateRequest,
    ): PageView {
        return new PageView(
            name: $pageUpdateRequest->name,
        );
    }
}
