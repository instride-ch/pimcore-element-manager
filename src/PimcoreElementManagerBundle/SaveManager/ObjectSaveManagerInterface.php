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

interface ObjectSaveManagerInterface
{
    public function preAdd(Concrete $object): void;

    public function postAdd(Concrete $object): void;

    public function preUpdate(Concrete $object): void;

    public function postUpdate(Concrete $object): void;

    public function preDelete(Concrete $object): void;

    public function postDelete(Concrete $object): void;

    public function validateOnSave(Concrete $object, bool $withDuplicatesCheck = true): bool;

    /**
     * @return ObjectSaveHandlerInterface[]
     */
    public function getSaveHandlers(): array;

    /**
     * @param ObjectSaveHandlerInterface[] $saveHandlers
     */
    public function setSaveHandlers(array $saveHandlers);

    public function addSaveHandler(ObjectSaveHandlerInterface $saveHandler): void;

    public function getOptions(): array;

    public function setOptions(array $options);
}
