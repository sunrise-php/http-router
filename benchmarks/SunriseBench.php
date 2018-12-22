<?php

namespace Sunrise\Http\Router\Benchs;

use Sunrise\Http\Router\RouteCollection;
use Sunrise\Http\Router\Router;
use Sunrise\Http\ServerRequest\ServerRequestFactory;

/**
 * @BeforeMethods({"init"})
 */
class SunriseBench
{
	protected $maxRoutes = 1000;
	protected $request;

	public function init()
	{
		$uri = \sprintf('/route/%d', $this->maxRoutes);

		$this->request = (new ServerRequestFactory)
		->createServerRequest('GET', $uri);
	}

	/**
	 * @Warmup(1)
	 * @Iterations(1000)
	 */
	public function benchSunriseMatch()
	{
		$map = new RouteCollection();

		for ($i = 1; $i <= $this->maxRoutes; $i++)
		{
			$id = \sprintf('route:%d', $i);

			// Усложним себе задачу...
			$map->get($id, '/route/{i}')->addPattern('i', "{$i}");
		}

		$router = new Router($map);
		$router->match($this->request);
	}
}
