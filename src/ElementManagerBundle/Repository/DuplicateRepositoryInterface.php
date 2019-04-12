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

namespace ElementManagerBundle\Repository;

use CoreShop\Component\Resource\Repository\RepositoryInterface;
use ElementManagerBundle\Model\DuplicateInterface;
use Pimcore\Model\DataObject\Concrete;

interface DuplicateRepositoryInterface extends RepositoryInterface
{
    /**
     * @param Concrete $concrete
     *
     * @return DuplicateInterface[]
     */
    public function findForObject(Concrete $concrete);

    /**
     * @param string $className
     * @param string $algorithm
     *
     * @return DuplicateInterface[]
     */
    public function findExactByAlgorithm(string $className, string $algorithm);

    /**
     * @param string $className
     * @param string $md5
     * @param int    $crc
     * @return DuplicateInterface|null
     */
    public function findForMd5AndCrc(string $className, string $md5, int $crc);

    /**
     * @param Concrete $concrete
     */
    public function deleteForObject(Concrete $concrete);
}
