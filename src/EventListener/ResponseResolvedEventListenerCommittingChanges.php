<?php

/**
 * It's free open-source software released under the MIT License.
 *
 * @author Anatoly Nekhay <afenric@gmail.com>
 * @copyright Copyright (c) 2018, Anatoly Nekhay
 * @license https://github.com/sunrise-php/http-router/blob/master/LICENSE
 * @link https://github.com/sunrise-php/http-router
 */

declare(strict_types=1);

namespace Sunrise\Http\Router\EventListener;

use Doctrine\Persistence\ManagerRegistry as EntityManagerRegistryInterface;
use ReflectionAttribute;
use Sunrise\Http\Router\Annotation\Commit;
use Sunrise\Http\Router\Event\ResponseResolvedEvent;

/**
 * @since 3.0.0
 */
final class ResponseResolvedEventListenerCommittingChanges
{

    /**
     * Constructor of the class
     *
     * @param EntityManagerRegistryInterface $entityManagerRegistry
     */
    public function __construct(private EntityManagerRegistryInterface $entityManagerRegistry)
    {
    }

    /**
     * Handles the given event
     *
     * @param ResponseResolvedEvent $event
     *
     * @return ResponseResolvedEvent
     */
    public function __invoke(ResponseResolvedEvent $event): ResponseResolvedEvent
    {
        /** @var list<ReflectionAttribute<Commit>> $attributes */
        $attributes = $event->getSource()->getAttributes(Commit::class);
        if ($attributes === []) {
            return $event;
        }

        foreach ($attributes as $attribute) {
            $commit = $attribute->newInstance();
            $entityManager = $this->entityManagerRegistry->getManager($commit->em);
            $entityManager->flush();
        }

        return $event;
    }
}
