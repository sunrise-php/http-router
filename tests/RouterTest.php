<?php

namespace Sunrise\Http\ServerRequest\Tests;

use Fig\Http\Message\RequestMethodInterface;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Sunrise\Http\Message\ResponseFactory;
use Sunrise\Http\Router\RouteInterface;
use Sunrise\Http\Router\Router;
use Sunrise\Http\Router\RouterInterface;
use Sunrise\Http\ServerRequest\ServerRequestFactory;
use Sunrise\Http\Router\Exception\BadRequestException;
use Sunrise\Http\Router\Exception\HttpException;
use Sunrise\Http\Router\Exception\HttpExceptionInterface;
use Sunrise\Http\Router\Exception\MethodNotAllowedException;
use Sunrise\Http\Router\Exception\PageNotFoundException;

class RouterTest extends TestCase
{
	public function testConstructor()
	{
		$router = new Router();

		$this->assertInstanceOf(RouterInterface::class, $router);
		$this->assertInstanceOf(RequestHandlerInterface::class, $router);
	}

	public function testCreateRoute()
	{
		$router = new Router();

		$action = $this->getRouteAction();
		$route = $router->add('home', '/', $action);

		$this->assertInstanceOf(RouteInterface::class, $route);
		$this->assertEquals('home', $route->getId());
		$this->assertEquals('/', $route->getPath());
		$this->assertEquals($action, $route->getAction());
		$this->assertEquals([], $route->getMethods());
	}

	public function testCreateRouteWithMethodHead()
	{
		$router = new Router();

		$action = $this->getRouteAction();
		$route = $router->head('home', '/', $action);

		$this->assertInstanceOf(RouteInterface::class, $route);
		$this->assertEquals('home', $route->getId());
		$this->assertEquals('/', $route->getPath());
		$this->assertEquals($action, $route->getAction());
		$this->assertEquals([RequestMethodInterface::METHOD_HEAD], $route->getMethods());
	}

	public function testCreateRouteWithMethodGet()
	{
		$router = new Router();

		$action = $this->getRouteAction();
		$route = $router->get('home', '/', $action);

		$this->assertInstanceOf(RouteInterface::class, $route);
		$this->assertEquals('home', $route->getId());
		$this->assertEquals('/', $route->getPath());
		$this->assertEquals($action, $route->getAction());
		$this->assertEquals([RequestMethodInterface::METHOD_GET], $route->getMethods());
	}

	public function testCreateRouteWithMethodPost()
	{
		$router = new Router();

		$action = $this->getRouteAction();
		$route = $router->post('home', '/', $action);

		$this->assertInstanceOf(RouteInterface::class, $route);
		$this->assertEquals('home', $route->getId());
		$this->assertEquals('/', $route->getPath());
		$this->assertEquals($action, $route->getAction());
		$this->assertEquals([RequestMethodInterface::METHOD_POST], $route->getMethods());
	}

	public function testCreateRouteWithMethodPut()
	{
		$router = new Router();

		$action = $this->getRouteAction();
		$route = $router->put('home', '/', $action);

		$this->assertInstanceOf(RouteInterface::class, $route);
		$this->assertEquals('home', $route->getId());
		$this->assertEquals('/', $route->getPath());
		$this->assertEquals($action, $route->getAction());
		$this->assertEquals([RequestMethodInterface::METHOD_PUT], $route->getMethods());
	}

	public function testCreateRouteWithMethodPatch()
	{
		$router = new Router();

		$action = $this->getRouteAction();
		$route = $router->patch('home', '/', $action);

		$this->assertInstanceOf(RouteInterface::class, $route);
		$this->assertEquals('home', $route->getId());
		$this->assertEquals('/', $route->getPath());
		$this->assertEquals($action, $route->getAction());
		$this->assertEquals([RequestMethodInterface::METHOD_PATCH], $route->getMethods());
	}

	public function testCreateRouteWithMethodDelete()
	{
		$router = new Router();

		$action = $this->getRouteAction();
		$route = $router->delete('home', '/', $action);

		$this->assertInstanceOf(RouteInterface::class, $route);
		$this->assertEquals('home', $route->getId());
		$this->assertEquals('/', $route->getPath());
		$this->assertEquals($action, $route->getAction());
		$this->assertEquals([RequestMethodInterface::METHOD_DELETE], $route->getMethods());
	}

	public function testCreateRouteWithMethodPurge()
	{
		$router = new Router();

		$action = $this->getRouteAction();
		$route = $router->purge('home', '/', $action);

		$this->assertInstanceOf(RouteInterface::class, $route);
		$this->assertEquals('home', $route->getId());
		$this->assertEquals('/', $route->getPath());
		$this->assertEquals($action, $route->getAction());
		$this->assertEquals([RequestMethodInterface::METHOD_PURGE], $route->getMethods());
	}

	public function testCreateRouteWithMethodOptions()
	{
		$router = new Router();

		$action = $this->getRouteAction();
		$route = $router->options('home', '/', $action);

		$this->assertInstanceOf(RouteInterface::class, $route);
		$this->assertEquals('home', $route->getId());
		$this->assertEquals('/', $route->getPath());
		$this->assertEquals($action, $route->getAction());
		$this->assertEquals([RequestMethodInterface::METHOD_OPTIONS], $route->getMethods());
	}

	public function testCreateRouteWithMethodTrace()
	{
		$router = new Router();

		$action = $this->getRouteAction();
		$route = $router->trace('home', '/', $action);

		$this->assertInstanceOf(RouteInterface::class, $route);
		$this->assertEquals('home', $route->getId());
		$this->assertEquals('/', $route->getPath());
		$this->assertEquals($action, $route->getAction());
		$this->assertEquals([RequestMethodInterface::METHOD_TRACE], $route->getMethods());
	}

	public function testCreateRouteWithMethodConnect()
	{
		$router = new Router();

		$action = $this->getRouteAction();
		$route = $router->connect('home', '/', $action);

		$this->assertInstanceOf(RouteInterface::class, $route);
		$this->assertEquals('home', $route->getId());
		$this->assertEquals('/', $route->getPath());
		$this->assertEquals($action, $route->getAction());
		$this->assertEquals([RequestMethodInterface::METHOD_CONNECT], $route->getMethods());
	}

	public function testCreateRouteWithMethodSafe()
	{
		$router = new Router();

		$action = $this->getRouteAction();
		$route = $router->safe('home', '/', $action);

		$this->assertInstanceOf(RouteInterface::class, $route);
		$this->assertEquals('home', $route->getId());
		$this->assertEquals('/', $route->getPath());
		$this->assertEquals($action, $route->getAction());
		$this->assertEquals([
			RequestMethodInterface::METHOD_HEAD,
			RequestMethodInterface::METHOD_GET,
		], $route->getMethods());
	}

	public function testCreateRouteWithMethodAny()
	{
		$router = new Router();

		$action = $this->getRouteAction();
		$route = $router->any('home', '/', $action);

		$this->assertInstanceOf(RouteInterface::class, $route);
		$this->assertEquals('home', $route->getId());
		$this->assertEquals('/', $route->getPath());
		$this->assertEquals($action, $route->getAction());
		$this->assertEquals([
			RequestMethodInterface::METHOD_HEAD,
			RequestMethodInterface::METHOD_GET,
			RequestMethodInterface::METHOD_POST,
			RequestMethodInterface::METHOD_PUT,
			RequestMethodInterface::METHOD_PATCH,
			RequestMethodInterface::METHOD_DELETE,
			RequestMethodInterface::METHOD_PURGE,
			RequestMethodInterface::METHOD_OPTIONS,
			RequestMethodInterface::METHOD_TRACE,
			RequestMethodInterface::METHOD_CONNECT,
		], $route->getMethods());
	}

	public function testMatch()
	{
		$router = new Router();

		$router->get('home', '/', $this->getRouteAction());

		$request = (new ServerRequestFactory)
		->createServerRequest('GET', '/');

		$route = $router->match($request);

		$this->assertEquals('home', $route->getId());
	}

	public function testMatchPageNotFoundException()
	{
		$this->expectException(PageNotFoundException::class);

		$router = new Router();

		$router->get('home', '/', $this->getRouteAction());

		$request = (new ServerRequestFactory)
		->createServerRequest('GET', '/404');

		$router->match($request);
	}

	public function testMatchMethodNotAllowedException()
	{
		$this->expectException(MethodNotAllowedException::class);

		$router = new Router();

		$router->safe('home', '/', $this->getRouteAction());

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

		$router->patch('post.update', '/post/{id}', $this->getRouteAction());

		$request = (new ServerRequestFactory)
		->createServerRequest('PATCH', '/post/100');

		$route = $router->match($request);

		$this->assertEquals('post.update', $route->getId());
		$this->assertArraySubset(['id' => '100'], $route->getAttributes());
	}

	public function testMatchWithSeveralRouteAttributes()
	{
		$router = new Router();

		$router->patch('post.update', '/post/{section}/{post}', $this->getRouteAction());

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

		$route = $router->patch('menu.item.move', '/menu/item/{id}/{direction}', $this->getRouteAction());
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

		$route = $router->delete('post.delete', '/post/{id}', $this->getRouteAction());
		$route->pattern('id', '\d+');

		$request = (new ServerRequestFactory)
		->createServerRequest('DELETE', '/post/a');

		$router->match($request);
	}

	public function testHandle()
	{
		$router = new Router();
		$router->middleware($this->getRouteMiddlewareFoo());
		$router->middleware($this->getRouteMiddlewareBar());

		$route = $router->get('home', '/', $this->getRouteAction());
		$route->middleware($this->getRouteMiddlewareBaz());
		$route->middleware($this->getRouteMiddlewareQux());

		$request = (new ServerRequestFactory)
		->createServerRequest('GET', '/');

		$response = $router->handle($request);

		$this->assertEquals([
			'route',
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

	private function getRouteAction() : callable
	{
		return function(ServerRequestInterface $request, ResponseInterface $response) : ResponseInterface
		{
			return $response->withAddedHeader('x-queue', 'route');
		};
	}

	private function getRouteMiddlewareFoo() : MiddlewareInterface
	{
		return new class implements MiddlewareInterface
		{
			public function process(ServerRequestInterface $request, RequestHandlerInterface $handler) : ResponseInterface
			{
				return $handler->handle($request)->withAddedHeader('x-queue', 'middleware-foo');
			}
		};
	}

	private function getRouteMiddlewareBar() : MiddlewareInterface
	{
		return new class implements MiddlewareInterface
		{
			public function process(ServerRequestInterface $request, RequestHandlerInterface $handler) : ResponseInterface
			{
				return $handler->handle($request)->withAddedHeader('x-queue', 'middleware-bar');
			}
		};
	}

	private function getRouteMiddlewareBaz() : MiddlewareInterface
	{
		return new class implements MiddlewareInterface
		{
			public function process(ServerRequestInterface $request, RequestHandlerInterface $handler) : ResponseInterface
			{
				return $handler->handle($request)->withAddedHeader('x-queue', 'middleware-baz');
			}
		};
	}

	private function getRouteMiddlewareQux() : MiddlewareInterface
	{
		return new class implements MiddlewareInterface
		{
			public function process(ServerRequestInterface $request, RequestHandlerInterface $handler) : ResponseInterface
			{
				return $handler->handle($request)->withAddedHeader('x-queue', 'middleware-qux');
			}
		};
	}
}
