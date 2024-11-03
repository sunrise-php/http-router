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

namespace Sunrise\Http\Router\Command;

use Sunrise\Http\Router\Loader\DescriptorLoader;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * @since 3.0.0
 */
#[AsCommand('router:clear-descriptors-cache', 'Clears the descriptors cache.')]
final class RouterClearDescriptorsCacheCommand extends Command
{
    public function __construct(
        private readonly DescriptorLoader $descriptorLoader,
    ) {
        parent::__construct();
    }

    /**
     * @inheritDoc
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->descriptorLoader->clearCache();

        $output->writeln('Ok');

        return self::SUCCESS;
    }
}
