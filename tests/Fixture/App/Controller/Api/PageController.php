<?php

declare(strict_types=1);

namespace Sunrise\Http\Router\Tests\Fixture\App\Controller\Api;

use Sunrise\Http\Router\Annotation\Consumes;
use Sunrise\Http\Router\Annotation\EncodableResponse;
use Sunrise\Http\Router\Annotation\GetApiRoute;
use Sunrise\Http\Router\Annotation\NamePrefix;
use Sunrise\Http\Router\Annotation\PathPrefix;
use Sunrise\Http\Router\Annotation\PostApiRoute;
use Sunrise\Http\Router\Annotation\Produces;
use Sunrise\Http\Router\Annotation\PutApiRoute;
use Sunrise\Http\Router\Annotation\RequestBody;
use Sunrise\Http\Router\Annotation\RequestQuery;
use Sunrise\Http\Router\Annotation\ResponseStatus;
use Sunrise\Http\Router\Annotation\Summary;
use Sunrise\Http\Router\Annotation\Tag;
use Sunrise\Http\Router\Dictionary\MediaType;
use Sunrise\Http\Router\Tests\Fixture\App\Dto\Page\PageCreateRequest;
use Sunrise\Http\Router\Tests\Fixture\App\Dto\Page\PageListRequest;
use Sunrise\Http\Router\Tests\Fixture\App\Dto\Page\PageUpdateRequest;
use Sunrise\Http\Router\Tests\Fixture\App\View\PageViewCollection;
use Sunrise\Http\Router\Tests\Fixture\App\View\PageView;

#[PathPrefix('/pages')]
#[NamePrefix('pages.')]
#[Tag('Pages')]
final class PageController extends AbstractController
{
    #[GetApiRoute('list')]
    #[Produces(MediaType::JSON)]
    #[EncodableResponse]
    #[Summary('Lists pages')]
    public function listPages(
        #[RequestQuery]
        PageListRequest $listRequest,
    ): PageViewCollection {
        return new PageViewCollection(
            new PageView('Page 1'),
            new PageView('Page 2'),
        );
    }

    #[PostApiRoute('create')]
    #[Consumes(MediaType::JSON)]
    #[Produces(MediaType::JSON)]
    #[ResponseStatus(201)]
    #[EncodableResponse]
    #[Summary('Creates a new page')]
    public function createPage(
        #[RequestBody]
        PageCreateRequest $createRequest,
    ): PageView {
        return new PageView($createRequest->name);
    }

    #[PutApiRoute('update', '/{id}')]
    #[Consumes(MediaType::JSON)]
    #[Produces(MediaType::JSON)]
    #[EncodableResponse]
    #[Summary('Updates a page by ID')]
    public function updatePage(
        #[RequestBody]
        PageUpdateRequest $updateRequest,
    ): PageView {
        return new PageView($updateRequest->name);
    }
}
