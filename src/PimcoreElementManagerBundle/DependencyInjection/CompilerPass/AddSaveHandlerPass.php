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

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

class AddSaveHandlerPass implements CompilerPassInterface
{
    /**
     * @inheritDoc
     */
    public function process(ContainerBuilder $container): void
    {
        foreach ($container->findTaggedServiceIds('element_manager.save_handler', true) as $id => $attributes) {
            if (!isset($attributes[0]['className'])) {
                throw new \InvalidArgumentException(\sprintf('Tagged Service `%s` needs to have `className` attribute.', $id));
            }

            $className = $attributes[0]['className'];

            if (!$container->hasDefinition(\sprintf('save_manager.%s', \strtolower($className)))) {
                continue;
            }

            $saveManagerDefinition = $container->getDefinition(\sprintf('save_manager.%s', \strtolower($className)));
            $saveManagerDefinition->addMethodCall('addSaveHandler', [new Reference($id)]);
        }
    }
}
