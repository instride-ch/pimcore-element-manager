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

namespace WVision\Bundle\ElementManagerBundle\DependencyInjection\CompilerPass;

use WVision\Bundle\ElementManagerBundle\DuplicateIndex\DataTransformer\ContainerDataTransformerFactory;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\Compiler\ServiceLocatorTagPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

class AddDataTransformersPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container)
    {
        if (!$container->hasDefinition(ContainerDataTransformerFactory::class)) {
            return;
        }

        $dataTransformers = [];

        foreach ($container->findTaggedServiceIds('element_manager.data_transformer', true) as $id => $attributes) {
            $definition = $container->getDefinition($id);

            if (!isset($attributes[0]['type'])) {
                $type = $definition->getClass();
            }
            else {
                $type = $attributes[0]['type'];
            }

            $dataTransformers[$type] = new Reference($id);
        }

        $container
            ->getDefinition(ContainerDataTransformerFactory::class)
            ->replaceArgument(0, ServiceLocatorTagPass::register($container, $dataTransformers))
        ;
    }
}
