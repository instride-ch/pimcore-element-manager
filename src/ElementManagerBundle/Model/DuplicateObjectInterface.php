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

namespace Wvision\Bundle\ElementManagerBundle\Model;

use CoreShop\Component\Resource\Model\ResourceInterface;
use CoreShop\Component\Resource\Model\TimestampableInterface;
use Pimcore\Model\DataObject\Concrete;

interface DuplicateObjectInterface extends ResourceInterface, TimestampableInterface
{
    /**
     * @return Concrete
     */
    public function getObject(): Concrete;

    /**
     * @param Concrete $object
     */
    public function setObject(Concrete $object): void;

    /**
     * @return DuplicateInterface
     */
    public function getDuplicate(): DuplicateInterface;

    /**
     * @param DuplicateInterface $duplicate
     */
    public function setDuplicate(DuplicateInterface $duplicate): void;

}
