<?php declare(strict_types=1);

namespace Sunrise\Http\Router\Tests\Fixture;

/**
 * Import classes
 */
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Sunrise\Http\Message\ResponseFactory;

/**
 * Import functions
 */
use function spl_object_hash;

/**
 * BlankMiddleware
 */
class BlankMiddleware implements MiddlewareInterface
{

    /**
     * @var bool
     */
    private $isBroken;

    /**
     * @var bool
     */
    private $isRunned = false;

    /**
     * @param bool $isBroken
     */
    public function __construct(bool $isBroken = false)
    {
        $this->isBroken = $isBroken;
    }

    /**
     * @return string
     */
    public function getHash() : string
    {
        return spl_object_hash($this);
    }

    /**
     * @return bool
     */
    public function isBroken() : bool
    {
        return $this->isBroken;
    }

    /**
     * @return bool
     */
    public function isRunned() : bool
    {
        return $this->isRunned;
    }

    /**
     * {@inheritDoc}
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler) : ResponseInterface
    {
        $this->isRunned = true;

        if ($this->isBroken) {
            return (new ResponseFactory)->createResponse();
        }

        return $handler->handle($request);
    }
}
