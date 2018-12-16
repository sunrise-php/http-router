<?php

namespace Sunrise\Http\Router\Tests;

use Fig\Http\Message\RequestMethodInterface;
use PHPUnit\Framework\TestCase;
use Psr\Http\Server\RequestHandlerInterface;
use Sunrise\Http\Message\ResponseFactory;
use Sunrise\Http\Router\Exception\BadRequestException;
use Sunrise\Http\Router\Exception\HttpException;
use Sunrise\Http\Router\Exception\HttpExceptionInterface;
use Sunrise\Http\Router\Exception\MethodNotAllowedException;
use Sunrise\Http\Router\Exception\PageNotFoundException;
use Sunrise\Http\Router\RouteCollection;
use Sunrise\Http\Router\Router;
use Sunrise\Http\Router\RouterInterface;
use Sunrise\Http\ServerRequest\ServerRequestFactory;

class RouterTest extends TestCase
{
	use HelpersInjectTest;

	public function testConstructor()
	{
		$router = new Router();

		$this->assertInstanceOf(RouterInterface::class, $router);
		$this->assertInstanceOf(RequestHandlerInterface::class, $router);
		$this->assertInstanceOf(RouteCollection::class, $router);
	}

	public function testMatch()
	{
		$router = new Router();

		$router->get('home', '/', $this->getRouteActionFoo());

		$request = (new ServerRequestFactory)
		->createServerRequest('GET', '/');

		$route = $router->match($request);

		$this->assertEquals('home', $route->getId());
	}

	public function testMatchPageNotFoundException()
	{
		$this->expectException(PageNotFoundException::class);

		$router = new Router();

		$router->get('home', '/', $this->getRouteActionFoo());

		$request = (new ServerRequestFactory)
		->createServerRequest('GET', '/404');

		$router->match($request);
	}

	public function testMatchMethodNotAllowedException()
	{
		$this->expectException(MethodNotAllowedException::class);

		$router = new Router();

		$router->safe('home', '/', $this->getRouteActionFoo());

		$request = (new ServerRequestFactory)
		->createServerRequest('POST', '/');

		try
		{
			$router->match($request);
		}
		catch (MethodNotAllowedException $e)
		{
			$allowedMethods[] = RequestMethodInterface::METHOD_HEAD;
			$allowedMethods[] = RequestMethodInterface::METHOD_GET;

			$this->assertEquals($allowedMethods, $e->getAllowedMethods());

			throw $e;
		}
		catch (\Throwable $e)
		{
			throw $e;
		}
	}

	public function testMatchWithRouteAttributes()
	{
		$router = new Router();

		$router->patch('post.update', '/post/{id}', $this->getRouteActionFoo());

		$request = (new ServerRequestFactory)
		->createServerRequest('PATCH', '/post/100');

		$route = $router->match($request);

		$this->assertEquals('post.update', $route->getId());
		$this->assertArraySubset(['id' => '100'], $route->getAttributes());
	}

	public function testMatchWithSeveralRouteAttributes()
	{
		$router = new Router();

		$router->patch('post.update', '/post/{section}/{post}', $this->getRouteActionFoo());

		$request = (new ServerRequestFactory)
		->createServerRequest('PATCH', '/post/100/200');

		$route = $router->match($request);

		$this->assertEquals('post.update', $route->getId());
		$this->assertArraySubset([
			'section' => '100',
			'post' => '200',
		], $route->getAttributes());
	}

	public function testMatchWithRoutePatterns()
	{
		$router = new Router();

		$route = $router->patch('menu.item.move', '/menu/item/{id}/{direction}', $this->getRouteActionFoo());
		$route->pattern('id', '\d+');
		$route->pattern('direction', 'up|down');

		$route = $router->match((new ServerRequestFactory)
		->createServerRequest('PATCH', '/menu/item/100/up'));

		$this->assertEquals('menu.item.move', $route->getId());
		$this->assertArraySubset([
			'id' => '100',
			'direction' => 'up',
		], $route->getAttributes());

		$route = $router->match((new ServerRequestFactory)
		->createServerRequest('PATCH', '/menu/item/100/down'));

		$this->assertEquals('menu.item.move', $route->getId());
		$this->assertArraySubset([
			'id' => '100',
			'direction' => 'down',
		], $route->getAttributes());
	}

	public function testMatchPageNotFoundExceptionDueInvalidAttributes()
	{
		$this->expectException(PageNotFoundException::class);

		$router = new Router();

		$route = $router->delete('post.delete', '/post/{id}', $this->getRouteActionFoo());
		$route->pattern('id', '\d+');

		$request = (new ServerRequestFactory)
		->createServerRequest('DELETE', '/post/a');

		$router->match($request);
	}

	public function testHandle()
	{
		$router = new Router();
		$router->middleware($this->getMiddlewareFoo());
		$router->middleware($this->getMiddlewareBar());

		$route = $router->get('home', '/', $this->getRouteActionFoo());
		$route->middleware($this->getMiddlewareBaz());
		$route->middleware($this->getMiddlewareQux());

		$request = (new ServerRequestFactory)
		->createServerRequest('GET', '/');

		$response = $router->handle($request);

		$this->assertEquals([
			'route-foo',
			'middleware-qux',
			'middleware-baz',
			'middleware-bar',
			'middleware-foo',
		], $response->getHeader('x-queue'));
	}

	public function testExceptions()
	{
		$request = (new ServerRequestFactory)
		->createServerRequest('GET', '/');

		$response = (new ResponseFactory)
		->createResponse(200);

		$httpException = new HttpException($request, $response);
		$this->assertInstanceOf(\RuntimeException::class, $httpException);
		$this->assertInstanceOf(HttpExceptionInterface::class, $httpException);
		$this->assertEquals($request, $httpException->getRequest());
		$this->assertEquals($response, $httpException->getResponse());

		$badRequestException = new BadRequestException($request);
		$this->assertInstanceOf(HttpException::class, $badRequestException);
		$this->assertEquals(400, $badRequestException->getResponse()->getStatusCode());

		$pageNotFoundException = new PageNotFoundException($request);
		$this->assertInstanceOf(HttpException::class, $pageNotFoundException);
		$this->assertEquals(404, $pageNotFoundException->getResponse()->getStatusCode());

		$methodNotAllowedException = new MethodNotAllowedException($request, ['HEAD', 'GET']);
		$this->assertInstanceOf(HttpException::class, $methodNotAllowedException);
		$this->assertEquals(['HEAD', 'GET'], $methodNotAllowedException->getAllowedMethods());
		$this->assertEquals(405, $methodNotAllowedException->getResponse()->getStatusCode());
	}
}
