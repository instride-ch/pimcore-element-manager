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
use Pimcore\Model\DataObject\Concrete;

class DuplicateRepository extends EntityRepository implements DuplicateRepositoryInterface
{
    /**
     * @inheritDoc
     */
    public function findForObject(Concrete $concrete)
    {
        return $this->createQueryBuilder('o')
            ->andWhere('o.object = :object')
            ->setParameter('object', $concrete->getId())
            ->getQuery()
            ->useResultCache(true)
            ->useQueryCache(true)
            ->getResult();
    }

    /**
     * @inheritDoc
     */
    public function findForMd5AndCrc(string $className, string $md5, int $crc)
    {
        return $this->createQueryBuilder('o')
            ->andWhere('o.md5 = :md5')
            ->andWhere('o.className = :className')
            ->andWhere('o.fieldsCrc = :crc')
            ->setParameter('md5', $md5)
            ->setParameter('crc', $crc)
            ->setParameter('className', $className)
            ->getQuery()
            ->useResultCache(true)
            ->useQueryCache(true)
            ->getOneOrNullResult();
    }

    /**
     * @inheritDoc
     */
    public function findExactByAlgorithm(string $className, string $algorithm)
    {
        switch($algorithm) {
            case 'metaphone':
                return $this->findExactByMetaphone($className);
            case 'soundex':
                return $this->findExactBySoundex($className);
        }

        throw new \InvalidArgumentException(sprintf('Undefined algorithm %s', $algorithm));
    }

    /**
     * @inheritDoc
     */
    public function findExactByMetaphone(string $className)
    {
        return $this->createQueryBuilder('m')
            ->where('m.metaphone IS NOT NULL and m.metaphone != \'\' AND m.className = :className')
            ->groupBy('m.metaphone')
            ->addGroupBy('m.className')
            ->having('count(m.id) > 1')
            ->setParameter('className', $className)
            ->getQuery()
            ->useResultCache(true)
            ->useQueryCache(true)
            ->getResult()
        ;
    }

    /**
     * @inheritDoc
     */
    public function findExactBySoundex(string $className)
    {
        return $this->createQueryBuilder('s')
            ->where('s.soundex IS NOT NULL and s.soundex != \'\' AND s.className = :className')
            ->groupBy('s.soundex')
            ->addGroupBy('s.className')
            ->having('count(s.id) > 1')
            ->setParameter('className', $className)
            ->getQuery()
            ->useResultCache(true)
            ->useQueryCache(true)
            ->getResult()
        ;
    }

    /**
     * @inheritDoc
     */
    public function deleteForObject(Concrete $concrete)
    {
        $query = $this->createQueryBuilder('o')
            ->delete()
            ->andWhere('o.object = :object')
            ->setParameter('object', $concrete->getId())
            ->getQuery();

        return $query->execute();
    }
}
