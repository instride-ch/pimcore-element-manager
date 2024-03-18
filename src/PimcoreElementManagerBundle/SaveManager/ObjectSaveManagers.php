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

namespace Instride\Bundle\PimcoreElementManagerBundle\SaveManager;

use Pimcore\Model\DataObject\Concrete;

class ObjectSaveManagers
{
    private array $saveManagers = [];

    public function hasSaveManager(Concrete $concrete): bool
    {
        return \array_key_exists($concrete->getClassName(), $this->saveManagers);
    }

    public function getSaveManger(Concrete $concrete): ObjectSaveManagerInterface
    {
        if (!$this->hasSaveManager($concrete)) {
            throw new \InvalidArgumentException(
                \sprintf('No Save Manager for Class %s found', $concrete->getClassName())
            );
        }

        return $this->saveManagers[$concrete->getClassName()];
    }

    public function addSaveManager(string $class, ObjectSaveManagerInterface $saveManager): void
    {
        $this->saveManagers[$class] = $saveManager;
    }
}
