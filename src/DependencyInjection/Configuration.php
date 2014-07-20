<?php

/**
 * This file is part of remote-block
 *
 * (c) Aaron Scherer <aequasi@gmail.com>
 *
 * This source file is subject to the license that is bundled
 * with this source code in the file LICENSE
 */

namespace Bldr\Block\Remote\DependencyInjection;

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
            ->addDefaultsIfNotSet()
            ->useAttributeAsKey('name')
            ->prototype('array')
                ->addDefaultsIfNotSet()
                ->children()
                    ->scalarNode('hostname')
                        ->isRequired()
                    ->end()
                    ->integerNode('port')
                        ->defaultValue(22)
                    ->end()
                    ->integerNode('timeout')
                        ->defaultValue(5)
                    ->end()
                    ->scalarNode('username')
                        ->isRequired()
                    ->end()
                    ->scalarNode('password')
                        ->info("Also the password for the private rsa key. DO NOT STORE THIS IN CVS")
                    ->end()
                    ->scalarNode('rsa_key')
                        ->info("Full location to this host's private rsa key file")
                    ->end()
                ->end()
            ->end()
        ;

        return $treeBuilder;
    }
}
 
