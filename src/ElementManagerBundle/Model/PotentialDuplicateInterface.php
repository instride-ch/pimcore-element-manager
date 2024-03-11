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
 * @copyright  Copyright (c) 2016-2022 w-vision AG (https://www.w-vision.ch)
 * @license    https://github.com/w-vision/ImportDefinitions/blob/master/gpl-3.0.txt GNU General Public License version 3 (GPLv3)
 */

namespace Wvision\Bundle\ElementManagerBundle\Model;

use CoreShop\Component\Resource\Model\ResourceInterface;
use CoreShop\Component\Resource\Model\TimestampableInterface;

interface PotentialDuplicateInterface extends ResourceInterface, TimestampableInterface
{
    /**
     * @return DuplicateObjectInterface
     */
    public function getDuplicateFrom(): DuplicateObjectInterface;

    /**
     * @param DuplicateObjectInterface $duplicateFrom
     */
    public function setDuplicateFrom(DuplicateObjectInterface $duplicateFrom): void;

    /**
     * @return DuplicateObjectInterface
     */
    public function getDuplicateTo(): DuplicateObjectInterface;

    /**
     * @param DuplicateObjectInterface $duplicateTo
     */
    public function setDuplicateTo(DuplicateObjectInterface $duplicateTo): void;

    /**
     * @return bool
     */
    public function getDeclined(): bool;

    /**
     * @param bool $declined
     */
    public function setDeclined(bool $declined): void;
}
