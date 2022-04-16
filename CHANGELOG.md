## v2.13.0

* Supports for events using the `symfony/event-dispatcher`.

## v2.14.0

* New method: `Route::getHolder():Reflector`;
* New method: `Router::resolveHostname(string):?string`;
* New method: `Router::getRoutesByHostname(string):array`;
* New method: `RouterBuilder::setEventDispatcher(?EventDispatcherInterface):void`.

## v2.15.0

* New middleware: `Sunrise\Http\Router\Middleware\JsonPayloadDecodingMiddleware`.
