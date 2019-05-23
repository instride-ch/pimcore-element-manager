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

namespace WVision\Bundle\ElementManagerBundle\DependencyInjection;

use CoreShop\Bundle\ResourceBundle\CoreShopResourceBundle;
use CoreShop\Component\Resource\Factory\Factory;
use WVision\Bundle\ElementManagerBundle\Model\Duplicate;
use WVision\Bundle\ElementManagerBundle\Model\DuplicateFalsePositive;
use WVision\Bundle\ElementManagerBundle\Model\DuplicateFalsePositiveInterface;
use WVision\Bundle\ElementManagerBundle\Model\DuplicateInterface;
use WVision\Bundle\ElementManagerBundle\Model\DuplicateObject;
use WVision\Bundle\ElementManagerBundle\Model\DuplicateObjectInterface;
use WVision\Bundle\ElementManagerBundle\Model\PotentialDuplicate;
use WVision\Bundle\ElementManagerBundle\Model\PotentialDuplicateInterface;
use WVision\Bundle\ElementManagerBundle\Repository\DuplicateObjectRepository;
use WVision\Bundle\ElementManagerBundle\Repository\DuplicateRepository;
use WVision\Bundle\ElementManagerBundle\Repository\PotentialDuplicateRepository;
use WVision\Bundle\ElementManagerBundle\SaveManager\NamingScheme\ExpressionNamingScheme;
use WVision\Bundle\ElementManagerBundle\SaveManager\ObjectSaveManager;
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

        $rootNode
            ->children()
                ->scalarNode('driver')->defaultValue(CoreShopResourceBundle::DRIVER_DOCTRINE_ORM)->end()
            ->end();

        $this->addDuplicationSection($rootNode);
        $this->addSaveManagerSection($rootNode);
        $this->addModelsSection($rootNode);

        return $treeBuilder;
    }

    private function addSaveManagerSection(ArrayNodeDefinition $rootNode)
    {
        $rootNode
            ->children()
                ->arrayNode('classes')
                    ->useAttributeAsKey('class')
                    ->arrayPrototype()
                        ->addDefaultsIfNotSet()
                        ->children()
                            ->scalarNode('save_manager_class')->defaultValue(ObjectSaveManager::class)->end()
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
                            ->arrayNode('duplicates_index')
                                ->children()
                                    ->booleanNode('enabled')->defaultFalse()->end()
                                    ->arrayNode('groups')
                                        ->useAttributeAsKey('name')
                                        ->arrayPrototype()
                                            ->children()
                                                ->scalarNode('name')->end()
                                                ->arrayNode('fields')
                                                    ->useAttributeAsKey('name')
                                                    ->variablePrototype()->end()
                                                ->end()
                                            ->end()
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
        ->end();
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

    /**
     * @param ArrayNodeDefinition $node
     */
    private function addModelsSection(ArrayNodeDefinition $node)
    {
        $node
            ->children()
                ->arrayNode('resources')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->arrayNode('duplicate')
                            ->addDefaultsIfNotSet()
                            ->children()
                                ->variableNode('options')->end()
                                ->arrayNode('classes')
                                    ->addDefaultsIfNotSet()
                                    ->children()
                                        ->scalarNode('model')->defaultValue(Duplicate::class)->cannotBeEmpty()->end()
                                        ->scalarNode('interface')->defaultValue(DuplicateInterface::class)->cannotBeEmpty()->end()
                                        //->scalarNode('admin_controller')->defaultValue(ResourceController::class)->cannotBeEmpty()->end()
                                        ->scalarNode('factory')->defaultValue(Factory::class)->cannotBeEmpty()->end()
                                        ->scalarNode('repository')->defaultValue(DuplicateRepository::class)->end()
                                    ->end()
                                ->end()
                            ->end()
                        ->end()
                        ->arrayNode('duplicate_false_positive')
                            ->addDefaultsIfNotSet()
                            ->children()
                                ->variableNode('options')->end()
                                ->arrayNode('classes')
                                    ->addDefaultsIfNotSet()
                                    ->children()
                                        ->scalarNode('model')->defaultValue(DuplicateFalsePositive::class)->cannotBeEmpty()->end()
                                        ->scalarNode('interface')->defaultValue(DuplicateFalsePositiveInterface::class)->cannotBeEmpty()->end()
                                        //->scalarNode('admin_controller')->defaultValue(ResourceController::class)->cannotBeEmpty()->end()
                                        ->scalarNode('factory')->defaultValue(Factory::class)->cannotBeEmpty()->end()
                                        ->scalarNode('repository')->cannotBeEmpty()->end()
                                    ->end()
                                ->end()
                            ->end()
                        ->end()
                        ->arrayNode('duplicate_object')
                            ->addDefaultsIfNotSet()
                            ->children()
                                ->variableNode('options')->end()
                                ->arrayNode('classes')
                                    ->addDefaultsIfNotSet()
                                    ->children()
                                        ->scalarNode('model')->defaultValue(DuplicateObject::class)->cannotBeEmpty()->end()
                                        ->scalarNode('interface')->defaultValue(DuplicateObjectInterface::class)->cannotBeEmpty()->end()
                                        //->scalarNode('admin_controller')->defaultValue(ResourceController::class)->cannotBeEmpty()->end()
                                        ->scalarNode('factory')->defaultValue(Factory::class)->cannotBeEmpty()->end()
                                        ->scalarNode('repository')->defaultValue(DuplicateObjectRepository::class)->end()
                                    ->end()
                                ->end()
                            ->end()
                        ->end()
                        ->arrayNode('potential_duplicate')
                            ->addDefaultsIfNotSet()
                            ->children()
                                ->variableNode('options')->end()
                                ->arrayNode('classes')
                                    ->addDefaultsIfNotSet()
                                    ->children()
                                        ->scalarNode('model')->defaultValue(PotentialDuplicate::class)->cannotBeEmpty()->end()
                                        ->scalarNode('interface')->defaultValue(PotentialDuplicateInterface::class)->cannotBeEmpty()->end()
                                        //->scalarNode('admin_controller')->defaultValue(ResourceController::class)->cannotBeEmpty()->end()
                                        ->scalarNode('factory')->defaultValue(Factory::class)->cannotBeEmpty()->end()
                                        ->scalarNode('repository')->defaultValue(PotentialDuplicateRepository::class)->end()
                                    ->end()
                                ->end()
                            ->end()
                        ->end()
                    ->end()
                ->end()
            ->end();
    }
}
