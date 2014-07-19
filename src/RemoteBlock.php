<?php

/**
 * This file is part of remote-block
 *
 * (c) Aaron Scherer <aequasi@gmail.com>
 *
 * This source file is subject to the license that is bundled
 * with this source code in the file LICENSE
 */

namespace Bldr\Block\Remote;

use Bldr\DependencyInjection\AbstractBlock;
use Symfony\Component\DependencyInjection\ContainerBuilder as SymfonyContainerBuilder;

/**
 * @author Aaron Scherer <aequasi@gmail.com>
 */
class RemoteBlock extends AbstractBlock
{
    /**
     * {@inheritDoc}
     */
    protected function getConfigurationClass()
    {
        return 'Bldr\Block\Remote\Configuration';
    }

    /**
     * {@inheritDoc}
     */
    protected function assemble(array $config, SymfonyContainerBuilder $container)
    {
        $container->setParameter('bldr.remote.hosts', $config);
        foreach ($config as $name => $host) {
            $container->setParameter('bldr.remote.hosts.'.$name, $host);
        }
    }
}
 