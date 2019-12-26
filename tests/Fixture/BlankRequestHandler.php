<?php declare(strict_types=1);

namespace Sunrise\Http\Router\Tests\Fixture;

/**
 * Import classes
 */
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Sunrise\Http\Message\ResponseFactory;

/**
 * BlankRequestHandler
 */
class BlankRequestHandler implements RequestHandlerInterface
{

    /**
     * @var bool
     */
    private $isRunned = false;

    /**
     * @var array
     */
    private $attributes = [];

    /**
     * @return bool
     */
    public function isRunned() : bool
    {
        return $this->isRunned;
    }

    /**
     * @return array
     */
    public function getAttributes() : array
    {
        return $this->attributes;
    }

    /**
     * @param mixed $key
     *
     * @return mixed
     */
    public function getAttribute($key)
    {
        return $this->attributes[$key] ?? null;
    }

    /**
     * {@inheritDoc}
     */
    public function handle(ServerRequestInterface $request) : ResponseInterface
    {
        $this->isRunned = true;
        $this->attributes = $request->getAttributes();

        return (new ResponseFactory)->createResponse();
    }

    /**
     * @param ServerRequestInterface $request
     *
     * @return ResponseInterface
     *
     * @link https://www.php.net/manual/ru/language.oop5.magic.php#object.invoke
     */
    public function __invoke(ServerRequestInterface $request) : ResponseInterface
    {
        return $this->handle($request);
    }
}
