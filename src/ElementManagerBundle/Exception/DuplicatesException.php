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

namespace WVision\Bundle\ElementManagerBundle\Exception;

use Pimcore\Model\DataObject\Concrete;
use Pimcore\Model\Element\ValidationException;

class DuplicatesException extends ValidationException
{
    /**
     * @var Concrete[]
     */
    private $duplicates;

    /**
     * @return Concrete[]
     */
    public function getDuplicates(): array
    {
        return $this->duplicates;
    }

    /**
     * @param Concrete[] $duplicates
     */
    public function setDuplicates($duplicates): void
    {
        $this->duplicates = $duplicates;
    }
}
