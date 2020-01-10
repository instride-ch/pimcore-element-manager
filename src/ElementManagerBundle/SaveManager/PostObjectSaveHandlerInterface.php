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
 * @copyright  Copyright (c) 2016-2020 w-vision AG (https://www.w-vision.ch)
 * @license    https://github.com/w-vision/ImportDefinitions/blob/master/gpl-3.0.txt GNU General Public License version 3 (GPLv3)
 */

namespace Wvision\Bundle\ElementManagerBundle\SaveManager;

use Pimcore\Model\DataObject\Concrete;

interface PostObjectSaveHandlerInterface
{
    /**
     * @param Concrete $object
     * @param array    $options
     */
    public function postPreSave(Concrete $object, array $options): void;

    /**
     * @param Concrete $object
     * @param array    $options
     */
    public function postPostSave(Concrete $object, array $options): void;

    /**
     * @param Concrete $object
     * @param array    $options
     */
    public function postPreAdd(Concrete $object, array $options): void;

    /**
     * @param Concrete $object
     * @param array    $options
     */
    public function postPostAdd(Concrete $object, array $options): void;

    /**
     * @param Concrete $object
     * @param array    $options
     */
    public function postPreUpdate(Concrete $object, array $options): void;

    /**
     * @param Concrete $object
     * @param array    $options
     */
    public function postPostUpdate(Concrete $object, array $options): void;

    /**
     * @param Concrete $object
     * @param array    $options
     */
    public function postPreDelete(Concrete $object, array $options): void;

    /**
     * @param Concrete $object
     * @param array    $options
     */
    public function postPostDelete(Concrete $object, array $options): void;
}
