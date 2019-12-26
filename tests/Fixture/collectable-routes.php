<?php declare(strict_types=1);

use Sunrise\Http\Router\Tests\Fixture\BlankRequestHandler;

$this->get('home', '/', new BlankRequestHandler());
