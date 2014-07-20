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

use Bldr\Block\Remote\DependencyInjection\CompilerPass\RemoteSubscriberCompilerPass;
use Bldr\DependencyInjection\AbstractBlock;
use Symfony\Component\DependencyInjection\ContainerBuilder as SymfonyContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

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
        return 'Bldr\Block\Remote\DependencyInjection\Configuration';
    }

    /**
     * {@inheritDoc}
     */
    public function getCompilerPasses()
    {
        return [new RemoteSubscriberCompilerPass()];
    }

    /**
     * {@inheritDoc}
     */
    protected function assemble(array $config, SymfonyContainerBuilder $container)
    {
        $this->addService('bldr_remote.event.remote', 'Bldr\Block\Remote\EventSubscriber\RemoteSubscriber')
            ->addArgument(new Reference('output'))
            ->addArgument($config);
    }
}
