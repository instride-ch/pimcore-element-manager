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

namespace Wvision\Bundle\ElementManagerBundle\SaveManager;

use Pimcore\Model\DataObject\Concrete;

interface ObjectSaveManagerInterface
{
    /**
     * @param Concrete $object
     */
    public function preAdd(Concrete $object): void;

    /**
     * @param Concrete $object
     */
    public function postAdd(Concrete $object): void;

    /**
     * @param Concrete $object
     */
    public function preUpdate(Concrete $object): void;

    /**
     * @param Concrete $object
     */
    public function postUpdate(Concrete $object): void;

    /**
     * @param Concrete $object
     */
    public function preDelete(Concrete $object): void;

    /**
     * @param Concrete $object
     */
    public function postDelete(Concrete $object): void;

    /**
     * @param Concrete $object
     * @param bool     $withDuplicatesCheck
     *
     * @return bool
     */
    public function validateOnSave(Concrete $object, bool $withDuplicatesCheck = true): bool;

    /**
     * @return ObjectSaveHandlerInterface[]
     */
    public function getSaveHandlers(): array;

    /**
     * @param ObjectSaveHandlerInterface[] $saveHandlers
     */
    public function setSaveHandlers(array $saveHandlers);

    /**
     * @param ObjectSaveHandlerInterface $saveHandler/**
     */
    public function addSaveHandler(ObjectSaveHandlerInterface $saveHandler): void;

    /**
     * @return array
     */
    public function getOptions(): array;

    /**
     * @param array $options
     */
    public function setOptions(array $options);
}
