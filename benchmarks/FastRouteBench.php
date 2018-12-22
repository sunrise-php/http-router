<?php

namespace Sunrise\Http\Router\Benchs;

use FastRoute\RouteParser\Std as RouteParser;
use FastRoute\DataGenerator\GroupCountBased as DataGenerator;
use FastRoute\Dispatcher\GroupCountBased as Dispatcher;
use FastRoute\RouteCollector;
use Sunrise\Http\ServerRequest\ServerRequestFactory;

/**
 * @BeforeMethods({"init"})
 */
class FastRouteBench
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
	public function benchFastRouteMatch()
	{
		$map = new RouteCollector(
			new RouteParser(),
			new DataGenerator()
		);

		for ($i = 1; $i <= $this->maxRoutes; $i++)
		{
			$path = \sprintf('/route/%d', $i);
			$action = function() {};

			$map->get($path, $action);
		}

		$dispatcher = new Dispatcher($map->getData());

		$dispatcher->dispatch(
			$this->request->getMethod(),
			$this->request->getUri()->getPath()
		);
	}
}
