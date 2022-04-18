<?php declare(strict_types=1);

namespace Sunrise\Http\Router\Tests\Fixtures\Controllers;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

final class BlankController extends AbstractController
{

    /**
     * @param ServerRequestInterface $request
     *
     * @return ResponseInterface
     *
     * @link https://www.php.net/manual/ru/language.oop5.magic.php#object.invoke
     */
    public function __invoke(ServerRequestInterface $request) : ResponseInterface
    {
        return $this->handle($request)->withStatus(305);
    }
}
