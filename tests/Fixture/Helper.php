<?php declare(strict_types=1);

namespace Sunrise\Http\Router\Tests\Fixture;

/**
 * Import functions
 */
use function get_class;

/**
 * Helper
 */
class Helper
{

    /**
     * @param iterable $routes
     * @return array
     */
    public static function routesToArray(iterable $routes) : array
    {
        $result = [];

        foreach ($routes as $route) {
            $item = [];
            $item['name'] = $route->getName();
            $item['path'] = $route->getPath();
            $item['methods'] = $route->getMethods();
            $item['requestHandler'] = get_class($route->getRequestHandler());
            $item['middlewares'] = [];
            $item['attributes'] = $route->getAttributes();

            foreach ($route->getMiddlewares() as $middleware) {
                $classname = get_class($middleware);

                if ($middleware instanceof NamedBlankMiddleware) {
                    $classname .= ':' . $middleware->getName();
                }

                $item['middlewares'][] = $classname;
            }

            $result[] = $item;
        }

        return $result;
    }

    /**
     * @param iterable $routes
     * @return array
     */
    public static function routesToArray204(iterable $routes) : array
    {
        $result = [];
        foreach ($routes as $route) {
            $result[] = [
                'summary' => $route->getSummary(),
                'description' => $route->getDescription(),
                'tags' => $route->getTags(),
            ];
        }

        return $result;
    }

    /**
     * @param iterable $routes
     * @return array
     */
    public static function routesToNames(iterable $routes) : array
    {
        $result = [];

        foreach ($routes as $route) {
            $result[] = $route->getName();
        }

        return $result;
    }
}
