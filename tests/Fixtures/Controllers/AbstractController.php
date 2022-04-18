<?php declare(strict_types=1);

namespace Sunrise\Http\Router\Tests\Fixtures\Controllers;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Sunrise\Http\Message\ResponseFactory;

abstract class AbstractController implements RequestHandlerInterface
{

    /**
     * Indicates if the controller was runned
     *
     * @var bool
     */
    private $isRunned = false;

    /**
     * The request that was handled by the called method
     *
     * @var ServerRequestInterface|null
     */
    private $request = null;

    /**
     * Checks if the controller was runned
     *
     * @return bool
     */
    public function isRunned() : bool
    {
        return $this->isRunned;
    }

    /**
     * Gets the request that was handled by the called method
     *
     * @return ServerRequestInterface|null
     */
    public function getRequest() : ?ServerRequestInterface
    {
        return $this->request;
    }

    /**
     * {@inheritdoc}
     */
    public function handle(ServerRequestInterface $request) : ResponseInterface
    {
        $this->isRunned = true;
        $this->request = $request;

        return (new ResponseFactory)->createResponse(200);
    }
}
