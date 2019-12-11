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

trait PostObjectSaveHandlerTrait
{
    /**
     * {@inheritdoc}
     */
    public function postPreSave(Concrete $object, array $options): void
    {
    }

    /**
     * {@inheritdoc}
     */
    public function postPostSave(Concrete $object, array $options): void
    {
    }

    /**
     * {@inheritdoc}
     */
    public function postPreAdd(Concrete $object, array $options): void
    {
    }

    /**
     * {@inheritdoc}
     */
    public function postPostAdd(Concrete $object, array $options): void
    {
    }

    /**
     * {@inheritdoc}
     */
    public function postPreUpdate(Concrete $object, array $options): void
    {
    }

    /**
     * {@inheritdoc}
     */
    public function postPostUpdate(Concrete $object, array $options): void
    {
    }

    /**
     * {@inheritdoc}
     */
    public function postPreDelete(Concrete $object, array $options): void
    {
    }

    /**
     * {@inheritdoc}
     */
    public function postPostDelete(Concrete $object, array $options): void
    {
    }
}
