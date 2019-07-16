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

namespace Wvision\Bundle\ElementManagerBundle\Repository;

use CoreShop\Component\Resource\Repository\RepositoryInterface;
use Wvision\Bundle\ElementManagerBundle\Model\DuplicateInterface;
use Wvision\Bundle\ElementManagerBundle\Model\DuplicateObjectInterface;
use Pimcore\Model\DataObject\Concrete;

interface DuplicateObjectRepositoryInterface extends RepositoryInterface
{
    /**
     * @param Concrete $concrete
     */
    public function deleteForObject(Concrete $concrete);

    /**
     * @param string $className
     *
     * @return DuplicateObjectInterface[]
     */
    public function findExactMatches(string $className): array;

    /**
     * @param int $currentId
     * @param string $algorithm
     * @param string $value
     *
     * @return DuplicateObjectInterface[]
     */
    public function findByDuplicateAndAlgorithmValue(int $currentId, string $algorithm, string $value): array;

    /**
     * @param DuplicateInterface $duplicate
     *
     * @return DuplicateObjectInterface[]
     */
    public function findByDuplicate(DuplicateInterface $duplicate): array;
}
