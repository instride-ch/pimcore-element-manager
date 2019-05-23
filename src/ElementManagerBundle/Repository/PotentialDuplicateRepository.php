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

namespace WVision\Bundle\ElementManagerBundle\Repository;

use CoreShop\Bundle\ResourceBundle\Doctrine\ORM\EntityRepository;
use WVision\Bundle\ElementManagerBundle\Model\DuplicateObjectInterface;
use Pimcore\Model\DataObject\Concrete;

class PotentialDuplicateRepository extends EntityRepository implements PotentialDuplicateRepositoryInterface
{
    /**
     * @inheritDoc
     */
    public function deleteAll()
    {
        $query = $this->createQueryBuilder('o')
            ->delete()
            ->getQuery();

        return $query->execute();
    }

    /**
     * @inheritDoc
     */
    public function findDuplication(DuplicateObjectInterface $duplicateObject1, DuplicateObjectInterface $duplicateObject2)
    {
        return $this->createQueryBuilder('o')
            ->andWhere('(o.duplicateFrom = :duplicate1 AND o.duplicateTo = :duplicate2)')
            ->orWhere('(o.duplicateFrom = :duplicate2 AND o.duplicateTo = :duplicate1)')
            ->setParameter('duplicate1', $duplicateObject1)
            ->setParameter('duplicate2', $duplicateObject2)
            ->getQuery()
            ->useResultCache(true)
            ->useQueryCache(true)
            ->getResult();
    }
}
