<?php declare(strict_types=1);

use Sunrise\Http\Router\Test\Fixture\Controllers\BlankController;

/** @var $this Sunrise\Http\Router\RouteCollector */

$this->get('bar', '/bar', new BlankController());
