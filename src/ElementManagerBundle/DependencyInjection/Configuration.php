<?php
/**
 * Element Manager
 *
 * LICENSE
 *
 * This source file is subject to the GNU General Public License version 3 (GPLv3)
 * For the full copyright and license information, please view the LICENSE.md and gpl-3.0.txt
 * files that are distributed with this source code.
 *
 * @copyright  Copyright (c) 2016-2018 w-vision AG (https://www.w-vision.ch)
 * @license    https://github.com/w-vision/ImportDefinitions/blob/master/gpl-3.0.txt GNU General Public License version 3 (GPLv3)
 */

namespace ElementManagerBundle\DependencyInjection;

use ElementManagerBundle\SaveManager\NamingScheme\ExpressionNamingScheme;
use ElementManagerBundle\SaveManager\ObjectSaveManager;
use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class Configuration implements ConfigurationInterface
{
    /**
     * {@inheritdoc}
     */
    public function getConfigTreeBuilder(): TreeBuilder
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('element_manager');

        $this->addDuplicationSection($rootNode);
        $this->addSaveManagerSection($rootNode);

        return $treeBuilder;
    }

    private function addSaveManagerSection(ArrayNodeDefinition $rootNode)
    {
        $rootNode
            ->children()
                ->arrayNode('save_manager')
                    ->useAttributeAsKey('class')
                    ->arrayPrototype()
                        ->addDefaultsIfNotSet()
                        ->children()
                            ->scalarNode('class')->defaultValue(ObjectSaveManager::class)->end()
                            ->arrayNode('naming_scheme')
                                ->children()
                                    ->scalarNode('service')->defaultValue(ExpressionNamingScheme::class)->end()
                                    ->booleanNode('enabled')->defaultFalse()->end()
                                    ->arrayNode('options')
                                        ->useAttributeAsKey('name')
                                        ->prototype('scalar')->end()
                                        ->children()
                                            ->scalarNode('parent_path')->end()
                                            ->scalarNode('archive_path')->end()
                                            ->scalarNode('scheme')->info('Expressions are allowed here')->end()
                                        ->end()
                                    ->end()
                                ->end()
                            ->end()
                            ->arrayNode('duplicates')
                                ->children()
                                    ->booleanNode('enabled_on_save')->defaultFalse()->end()
                                    ->arrayNode('options')
                                        ->useAttributeAsKey('name')
                                        ->prototype('scalar')->end()
                                    ->end()
                                ->end()
                            ->end()
                            ->arrayNode('validations')
                                ->children()
                                    ->booleanNode('enabled_on_save')->defaultTrue()->end()
                                    ->arrayNode('options')
                                        ->useAttributeAsKey('name')
                                        ->prototype('scalar')->end()
                                        ->children()
                                            ->scalarNode('group')->defaultValue('element_manager')->end()
                                        ->end()
                                    ->end()
                                ->end()
                            ->end()
                            ->arrayNode('save_handlers')
                                ->useAttributeAsKey('service')
                                ->prototype('scalar')->end()
                            ->end()
                        ->end()
                    ->end()
                ->end()
            ->end()
        ;
    }

    private function addDuplicationSection(ArrayNodeDefinition $rootNode)
    {
        $rootNode
            ->children()
                ->arrayNode('duplication')
                    ->info('duplication configuration')
                    ->children()
                        ->arrayNode('mapping')
                            ->addDefaultsIfNotSet()
                            ->fixXmlConfig('path')
                            ->children()
                                ->arrayNode('paths')
                                    ->defaultValue([])
                                    ->prototype('scalar')->end()
                                ->end()
                            ->end()
                        ->end()
                    ->end()
                ->end()
            ->end()
        ;
    }
}
