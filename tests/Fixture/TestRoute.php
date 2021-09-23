<?php declare(strict_types=1);

namespace Sunrise\Http\Router\Tests\Fixture;

/**
 * Import classes
 */
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Sunrise\Http\Router\Route as BaseRoute;

/**
 * Import functions
 */
use function uniqid;
use function strtoupper;

/**
 * TestRoute
 */
class TestRoute extends BaseRoute
{

    /**
     * @var int
     */
    public const WITH_BROKEN_MIDDLEWARE = 1;
    public const WITHOUT_MIDDLEWARES = 2;

    /**
     * Constructor of the class
     *
     * @param int $flags
     */
    public function __construct(int $flags = 0)
    {
        parent::__construct(
            self::getTestRouteName($flags),
            self::getTestRoutePath($flags),
            self::getTestRouteMethods($flags),
            self::getTestRouteRequestHandler($flags),
            self::getTestRouteMiddlewares($flags),
            self::getTestRouteAttributes($flags)
        );
    }

    /**
     * @return string
     */
    public static function getTestRouteName(int $flags = 0) : string
    {
        return uniqid() . '.' . uniqid() . '.' . uniqid();
    }

    /**
     * @return string
     */
    public static function getTestRoutePath(int $flags = 0) : string
    {
        return '/' . uniqid() . '/' . uniqid() . '/' . uniqid();
    }

    /**
     * @return string[]
     */
    public static function getTestRouteMethods(int $flags = 0) : array
    {
        return [
            strtoupper(uniqid('verb_')),
            strtoupper(uniqid('verb_')),
            strtoupper(uniqid('verb_')),
        ];
    }

    /**
     * @return RequestHandlerInterface
     */
    public static function getTestRouteRequestHandler(int $flags = 0) : RequestHandlerInterface
    {
        return new BlankRequestHandler();
    }

    /**
     * @return MiddlewareInterface[]
     */
    public static function getTestRouteMiddlewares(int $flags = 0) : array
    {
        if ($flags & self::WITHOUT_MIDDLEWARES) {
            return [];
        }

        $middlewares = [new BlankMiddleware()];

        if ($flags & self::WITH_BROKEN_MIDDLEWARE) {
            $middlewares[] = new BlankMiddleware(true);
        } else {
            $middlewares[] = new BlankMiddleware();
        }

        $middlewares[] = new BlankMiddleware();

        return $middlewares;
    }

    /**
     * @return array
     */
    public static function getTestRouteAttributes(int $flags = 0) : array
    {
        return [
            uniqid('attr_') => uniqid(),
            uniqid('attr_') => uniqid(),
            uniqid('attr_') => uniqid(),
        ];
    }
}
