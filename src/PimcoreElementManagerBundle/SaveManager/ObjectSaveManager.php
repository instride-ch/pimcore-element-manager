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

class ObjectSaveManager implements ObjectSaveManagerInterface
{
    /**
     * @var ObjectSaveHandlerInterface[]
     */
    protected array $saveHandlers = [];
    protected array $options = [];

    public function preAdd(Concrete $object): void
    {
        $this->applySaveHandlers($object, 'preAdd');
    }

    public function postAdd(Concrete $object): void
    {
        $this->applySaveHandlers($object, 'postAdd');
    }

    public function preUpdate(Concrete $object): void
    {
        $this->applySaveHandlers($object, 'preUpdate');
    }

    public function postUpdate(Concrete $object): void
    {
        $this->applySaveHandlers($object, 'postUpdate');
    }

    public function preDelete(Concrete $object): void
    {
        $this->applySaveHandlers($object, 'preDelete');
    }

    public function postDelete(Concrete $object): void
    {
        $this->applySaveHandlers($object, 'postDelete');
    }

    public function validateOnSave(Concrete $object, bool $withDuplicatesCheck = true): bool
    {
        return true;
    }

    /**
     * @inheritDoc
     */
    public function getSaveHandlers(): array
    {
        return $this->saveHandlers;
    }

    /**
     * @inheritDoc
     */
    public function setSaveHandlers(array $saveHandlers): void
    {
        $this->saveHandlers = $saveHandlers;
    }

    public function addSaveHandler(ObjectSaveHandlerInterface $saveHandler): void
    {
        $this->saveHandlers[] = $saveHandler;
    }

    public function getOptions(): array
    {
        return $this->options;
    }

    public function setOptions(array $options): void
    {
        $this->options = $options;
    }

    private function applySaveHandlers(Concrete $concrete, string $saveHandlerMethod): void
    {
        $saveHandlers = $this->getSaveHandlers();
        $postSaveMethod = 'post' . \ucfirst($saveHandlerMethod);

        foreach ($saveHandlers as $handler) {
            if (\method_exists($handler, $saveHandlerMethod)) {
                $handler->{$saveHandlerMethod}($concrete, $this->options);
            }

            if (\in_array($saveHandlerMethod, ['preAdd', 'preUpdate'], true)) {
                $handler->preSave($concrete, $this->options);
            }

            if (\in_array($saveHandlerMethod, ['postUpdate', 'postAdd'], true)) {
                $handler->postSave($concrete, $this->options);
            }
        }

        foreach ($saveHandlers as $handler) {
            if (!$handler instanceof PostObjectSaveHandlerInterface) {
                continue;
            }

            if (\method_exists($handler, $postSaveMethod)) {
                $handler->{$postSaveMethod}($concrete, $this->options);
            }

            if (\in_array($saveHandlerMethod, ['preAdd', 'preUpdate'], true)) {
                $handler->postPreSave($concrete, $this->options);
            }

            if (\in_array($saveHandlerMethod, ['postUpdate', 'postAdd'], true)) {
                $handler->postPostSave($concrete, $this->options);
            }
        }
    }
}
