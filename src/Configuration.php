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

use Symfony\Component\Config\Definition\ConfigurationInterface;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;

/**
 * @author Aaron Scherer <aequasi@gmail.com>
 */
class Configuration implements ConfigurationInterface
{
    /**
     * {@inheritDoc}
     */
    public function getConfigTreeBuilder()
    {

        $treeBuilder = new TreeBuilder();
        $rootNode    = $treeBuilder->root('remote');

        $rootNode
            ->useAttributeAsKey('name')
            ->prototype('array')
                ->children()
                    ->scalarNode('hostname')
                        ->isRequired()
                    ->end()
                    ->integerNode('port')
                        ->defaultValue(22)
                    ->end()
                    ->scalarNode('username')
                        ->isRequired()
                    ->end()
                    ->scalarNode('password')
                        ->isRequired()
                        ->info("DO NOT STORE THIS IN CVS")
                    ->end()
                    ->scalarNode('privateKey')
                        ->info("Full location to this host's private key file")
                    ->end()
                ->end()
            ->end()
        ;

        return $treeBuilder;
    }
}
 