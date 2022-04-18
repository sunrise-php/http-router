<?php declare(strict_types=1);

namespace Sunrise\Http\Router\Tests\Fixtures;

use Sunrise\Http\Router\Route as BaseRoute;

use function uniqid;

final class Route extends BaseRoute
{

    /**
     * Constructor of the class
     *
     * @param string|null $name
     * @param string|null $path
     * @param string[]|null $methods
     */
    public function __construct(?string $name = null, ?string $path = null, ?array $methods = null)
    {
        $uid = uniqid('');

        $name = $name ?? 'route.' . $uid;
        $path = $path ?? '/route/' . $uid;

        $methods = $methods ?? [
            uniqid('verb_'),
            uniqid('verb_'),
            uniqid('verb_'),
        ];

        parent::__construct($name, $path, $methods, new Controllers\BlankController());
    }
}
