<?php
/**
 * Element Manager.
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

namespace Wvision\Bundle\ElementManagerBundle\DependencyInjection\CompilerPass;

use InvalidArgumentException;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

class AddSaveHandlerPass implements CompilerPassInterface
{
    /**
     * {@inheritdoc}
     */
    public function process(ContainerBuilder $container): void
    {
        foreach ($container->findTaggedServiceIds('element_manager.save_handler', true) as $id => $attributes) {
            if (!isset($attributes[0]['className'])) {
                throw new InvalidArgumentException('Tagged Service `' . $id . '` needs to have `className` attribute.');
            }

            $className = $attributes[0]['className'];

            if (!$container->hasDefinition(sprintf('save_manager.%s', strtolower($className)))) {
                continue;
            }

            $saveManagerDefinition = $container->getDefinition(sprintf('save_manager.%s', strtolower($className)));
            $saveManagerDefinition->addMethodCall('addSaveHandler', [new Reference($id)]);
        }
    }
}
