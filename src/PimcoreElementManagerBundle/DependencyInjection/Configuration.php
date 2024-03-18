<?php

declare(strict_types=1);

/**
 * Pimcore Element Manager.
 *
 * LICENSE
 *
 * This source file is subject to the GNU General Public License version 3 (GPLv3)
 * For the full copyright and license information, please view the LICENSE.md and gpl-3.0.txt
 * files that are distributed with this source code.
 *
 * @copyright 2024 instride AG (https://instride.ch)
 * @license   https://github.com/instride-ch/pimcore-element-manager/blob/main/gpl-3.0.txt GNU General Public License version 3 (GPLv3)
 */

namespace Instride\Bundle\PimcoreElementManagerBundle\DependencyInjection;

use CoreShop\Bundle\ResourceBundle\CoreShopResourceBundle;
use CoreShop\Component\Resource\Factory\Factory;
use Instride\Bundle\PimcoreElementManagerBundle\Controller\Admin\DuplicatesIndexController;
use Instride\Bundle\PimcoreElementManagerBundle\Model\Duplicate;
use Instride\Bundle\PimcoreElementManagerBundle\Model\DuplicateFalsePositive;
use Instride\Bundle\PimcoreElementManagerBundle\Model\DuplicateFalsePositiveInterface;
use Instride\Bundle\PimcoreElementManagerBundle\Model\DuplicateInterface;
use Instride\Bundle\PimcoreElementManagerBundle\Model\DuplicateObject;
use Instride\Bundle\PimcoreElementManagerBundle\Model\DuplicateObjectInterface;
use Instride\Bundle\PimcoreElementManagerBundle\Model\PotentialDuplicate;
use Instride\Bundle\PimcoreElementManagerBundle\Model\PotentialDuplicateInterface;
use Instride\Bundle\PimcoreElementManagerBundle\Repository\DuplicateObjectRepository;
use Instride\Bundle\PimcoreElementManagerBundle\Repository\DuplicateRepository;
use Instride\Bundle\PimcoreElementManagerBundle\Repository\PotentialDuplicateRepository;
use Instride\Bundle\PimcoreElementManagerBundle\SaveManager\NamingScheme\ExpressionNamingScheme;
use Instride\Bundle\PimcoreElementManagerBundle\SaveManager\ObjectSaveManager;
use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class Configuration implements ConfigurationInterface
{
    /**
     * @inheritDoc
     */
    public function getConfigTreeBuilder(): TreeBuilder
    {
        $treeBuilder = new TreeBuilder('element_manager');
        $rootNode = $treeBuilder->getRootNode();

        $rootNode
            ->children()
                ->scalarNode('driver')->defaultValue(CoreShopResourceBundle::DRIVER_DOCTRINE_ORM)->end()
            ->end();

        $this->addDuplicationSection($rootNode);
        $this->addSaveManagerSection($rootNode);
        $this->addModelsSection($rootNode);
        $this->addPimcoreResourcesSection($rootNode);

        return $treeBuilder;
    }

    /**
     * @param ArrayNodeDefinition $rootNode
     */
    private function addSaveManagerSection(ArrayNodeDefinition $rootNode): void
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
                            ->arrayNode('unique_key')
                                ->children()
                                    ->booleanNode('enabled')->defaultFalse()->end()
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
                                    ->arrayNode('list_fields')
                                        ->prototype('array')
                                            ->prototype('scalar')
                                            ->end()
                                        ->end()
                                        ->defaultValue([['id', 'className']])
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

    /**
     * @param ArrayNodeDefinition $rootNode
     */
    private function addDuplicationSection(ArrayNodeDefinition $rootNode): void
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
            ->end();
    }

    /**
     * @param ArrayNodeDefinition $node
     */
    private function addPimcoreResourcesSection(ArrayNodeDefinition $node)
    {
        $node
            ->children()
                ->arrayNode('pimcore_admin')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->arrayNode('js')
                            ->useAttributeAsKey('name')
                            ->prototype('scalar')->end()
                        ->end()
                        ->arrayNode('css')
                            ->useAttributeAsKey('name')
                            ->prototype('scalar')->end()
                        ->end()
                        ->arrayNode('editmode_js')
                            ->useAttributeAsKey('name')
                            ->prototype('scalar')->end()
                        ->end()
                        ->arrayNode('editmode_css')
                            ->useAttributeAsKey('name')
                            ->prototype('scalar')->end()
                        ->end()
                        ->scalarNode('permissions')
                            ->cannotBeOverwritten()
                            ->defaultValue(['index', 'filter'])
                        ->end()
                    ->end()
                ->end()
            ->end();
    }

    /**
     * @param ArrayNodeDefinition $node
     */
    private function addModelsSection(ArrayNodeDefinition $node): void
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
                                        ->scalarNode('admin_controller')->defaultValue(DuplicatesIndexController::class)->cannotBeEmpty()->end()
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
