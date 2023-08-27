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
use Sunrise\Http\Router\Exception\LogicException;
use Sunrise\Http\Router\ResponseResolving\ResponseResolutioner;

use function sprintf;

/**
 * @since 3.0.0
 */
final class CommitEventListener
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
     *
     * @throws LogicException
     */
    public function __invoke(ResponseResolvedEvent $event): ResponseResolvedEvent
    {
        $source = $event->getSource();

        /** @var list<ReflectionAttribute<Commit>> $attributes */
        $attributes = $source->getAttributes(Commit::class);
        if ($attributes === []) {
            return $event;
        }

        $commit = $attributes[0]->newInstance();

        $entityManagerNames = $this->entityManagerRegistry->getManagerNames();
        if (isset($commit->em) && !isset($entityManagerNames[$commit->em])) {
            throw new LogicException(sprintf(
                'The #[Commit] attribute cannot be applied to the source {%s}, ' .
                'because the specified entity manager "%s" does not exist.',
                ResponseResolutioner::stringifySource($source),
                $commit->em,
            ));
        }

        $this->entityManagerRegistry->getManager($commit->em)->flush();

        return $event;
    }
}
