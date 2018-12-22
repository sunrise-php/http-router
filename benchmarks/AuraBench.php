<?php

namespace Sunrise\Http\Router\Benchs;

use Aura\Router\RouterContainer;
use Sunrise\Http\ServerRequest\ServerRequestFactory;

/**
 * @BeforeMethods({"init"})
 */
class AuraBench
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
	public function benchAuraMatch()
	{
		$routerContainer = new RouterContainer();

		$map = $routerContainer->getMap();

		for ($i = 1; $i <= $this->maxRoutes; $i++)
		{
			$id = \sprintf('route:%d', $i);
			$path = \sprintf('/route/%d', $i);
			$action = function() {};

			$map->get($id, $path, $action);
		}

		$matcher = $routerContainer->getMatcher();
		$matcher->match($this->request);
	}
}
