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

namespace ElementManagerBundle\EventListener;

use ElementManagerBundle\SaveManager\ObjectSaveManagers;
use Pimcore\Event\Model\DataObjectEvent;
use Pimcore\Event\Model\ElementEventInterface;
use Pimcore\Model\DataObject;
use Pimcore\Model\Element;

class ObjectEventListener
{
    /**
     * @var ObjectSaveManagers
     */
    private $saveManagers;

    /**
     * @param ObjectSaveManagers $saveManagers
     */
    public function __construct(ObjectSaveManagers $saveManagers)
    {
        $this->saveManagers = $saveManagers;
    }

    /**
     * @param ElementEventInterface $event
     * @throws Element\ValidationException
     */
    public function onPreUpdate(ElementEventInterface $event): void
    {
        if (!$event instanceof DataObjectEvent) {
            return;
        }

        $object = $event->getObject();

        if (!$object instanceof DataObject\Concrete) {
            return;
        }

        if ($this->saveManagers->hasSaveManager($object)) {
            $this->saveManagers->getSaveManger($object)->preUpdate($object);
        }
    }

    /**
     * @param ElementEventInterface $event
     */
    public function onPostUpdate(ElementEventInterface $event): void
    {
        if (!$event instanceof DataObjectEvent) {
            return;
        }

        $object = $event->getObject();

        if (!$object instanceof DataObject\Concrete) {
            return;
        }

        if ($this->saveManagers->hasSaveManager($object)) {
            $this->saveManagers->getSaveManger($object)->postUpdate($object);
        }
    }

    /**
     * @param ElementEventInterface $event
     * @throws Element\ValidationException
     */
    public function onPreAdd(ElementEventInterface $event): void
    {
        if (!$event instanceof DataObjectEvent) {
            return;
        }

        $object = $event->getObject();

        if (!$object instanceof DataObject\Concrete) {
            return;
        }

        if ($this->saveManagers->hasSaveManager($object)) {
            $this->saveManagers->getSaveManger($object)->preAdd($object);
        }
    }

    /**
     * @param ElementEventInterface $event
     */
    public function onPostAdd(ElementEventInterface $event): void
    {
        if (!$event instanceof DataObjectEvent) {
            return;
        }

        $object = $event->getObject();

        if (!$object instanceof DataObject\Concrete) {
            return;
        }

        if ($this->saveManagers->hasSaveManager($object)) {
            $this->saveManagers->getSaveManger($object)->postAdd($object);
        }
    }

    /**
     * @param ElementEventInterface $event
     */
    public function onPreDelete(ElementEventInterface $event): void
    {
        if (!$event instanceof DataObjectEvent) {
            return;
        }

        $object = $event->getObject();

        if (!$object instanceof DataObject\Concrete) {
            return;
        }

        if ($this->saveManagers->hasSaveManager($object)) {
            $this->saveManagers->getSaveManger($object)->preDelete($object);
        }
    }

    /**
     * @param ElementEventInterface $event
     */
    public function onPostDelete(ElementEventInterface $event): void
    {
        if (!$event instanceof DataObjectEvent) {
            return;
        }

        $object = $event->getObject();

        if (!$object instanceof DataObject\Concrete) {
            return;
        }

        if ($this->saveManagers->hasSaveManager($object)) {
            $this->saveManagers->getSaveManger($object)->postDelete($object);
        }
    }
}
