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

namespace Instride\Bundle\PimcoreElementManagerBundle\EventListener;

use Instride\Bundle\PimcoreElementManagerBundle\SaveManager\ObjectSaveManagers;
use Pimcore\Event\Model\DataObjectEvent;
use Pimcore\Event\Model\ElementEventInterface;
use Pimcore\Model\DataObject;

class ObjectEventListener
{
    public function __construct(private readonly ObjectSaveManagers $saveManagers) {}

    /**
     * @param ElementEventInterface $event
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
