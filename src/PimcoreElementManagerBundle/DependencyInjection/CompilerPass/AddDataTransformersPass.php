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

namespace Instride\Bundle\PimcoreElementManagerBundle\DependencyInjection\CompilerPass;

use Instride\Bundle\PimcoreElementManagerBundle\DuplicateIndex\DataTransformer\ContainerDataTransformerFactory;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\Compiler\ServiceLocatorTagPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

class AddDataTransformersPass implements CompilerPassInterface
{
    /**
     * @inheritDoc
     */
    public function process(ContainerBuilder $container): void
    {
        if (!$container->hasDefinition(ContainerDataTransformerFactory::class)) {
            return;
        }

        $dataTransformers = [];
        $services = $container->findTaggedServiceIds('pimcore_element_manager.data_transformer', true);

        foreach ($services as $id => $attributes) {
            $definition = $container->getDefinition($id);
            $type = $attributes[0]['type'] ?? $definition->getClass();

            $dataTransformers[$type] = new Reference($id);
        }

        $container
            ->getDefinition(ContainerDataTransformerFactory::class)
            ->replaceArgument(0, ServiceLocatorTagPass::register($container, $dataTransformers));
    }
}
