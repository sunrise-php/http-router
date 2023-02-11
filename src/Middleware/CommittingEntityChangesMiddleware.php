<?php declare(strict_types=1);

/**
 * It's free open-source software released under the MIT License.
 *
 * @author Anatoly Nekhay <afenric@gmail.com>
 * @copyright Copyright (c) 2018, Anatoly Nekhay
 * @license https://github.com/sunrise-php/http-router/blob/master/LICENSE
 * @link https://github.com/sunrise-php/http-router
 */

namespace Sunrise\Http\Router\Middleware;

/**
 * Import classes
 */
use Doctrine\Persistence\ManagerRegistry as EntityManagerRegistryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

/**
 * CommittingEntityChangesMiddleware
 *
 * @since 3.0.0
 */
final class CommittingEntityChangesMiddleware implements MiddlewareInterface
{

    /**
     * @var EntityManagerRegistryInterface
     */
    private EntityManagerRegistryInterface $entityManagerRegistry;

    /**
     * @var list<string>
     */
    private array $entityManagerNames;

    /**
     * @param EntityManagerRegistryInterface $entityManagerRegistry
     * @param list<string> $entityManagerNames
     */
    public function __construct(
        EntityManagerRegistryInterface $entityManagerRegistry,
        array $entityManagerNames = []
    ) {
        $this->entityManagerRegistry = $entityManagerRegistry;
        $this->entityManagerNames = $entityManagerNames;
    }

    /**
     * {@inheritdoc}
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $response = $handler->handle($request);

        if (empty($this->entityManagerNames)) {
            $this->entityManagerRegistry->getManager()->flush();
        }

        foreach ($this->entityManagerNames as $entityManagerName) {
            $this->entityManagerRegistry->getManager($entityManagerName)->flush();
        }

        return $response;
    }
}
