<?php declare(strict_types=1);

use Sunrise\Http\Router\Tests\Fixtures\Controllers\BlankController;

/** @var $this Sunrise\Http\Router\RouteCollector */

$this->get('bar', '/bar', new BlankController());
