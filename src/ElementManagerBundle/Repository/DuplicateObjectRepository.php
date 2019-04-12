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

use CoreShop\Bundle\ResourceBundle\Doctrine\ORM\EntityRepository;
use ElementManagerBundle\Model\DuplicateInterface;
use Pimcore\Model\DataObject\Concrete;

class DuplicateObjectRepository extends EntityRepository implements DuplicateObjectRepositoryInterface
{
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

    /**
     * @inheritDoc
     */
    public function findByDuplicateAndAlgorithmValue(string $algorithm, string $value): array
    {
        switch($algorithm) {
            case 'metaphone':
                return $this->findByDuplicateAndMetaphone($value);
            case 'soundex':
                return $this->findByDuplicateAndSoundex($value);
        }

        throw new \InvalidArgumentException(sprintf('Undefined algorithm %s', $algorithm));
    }

    /**
     * @inheritDoc
     */
    public function findByDuplicateAndMetaphone(string $metaphone): array
    {
        return $this->createQueryBuilder('o')
            ->innerJoin('o.duplicate', 'duplicate')
            ->where('duplicate.metaphone = :metaphone')
            ->setParameter('metaphone', $metaphone)
            ->getQuery()
            ->useResultCache(true)
            ->useQueryCache(true)
            ->getResult();
    }

    /**
     * @inheritDoc
     */
    public function findByDuplicateAndSoundex(string $soundex): array
    {
        return $this->createQueryBuilder('o')
            ->innerJoin('o.duplicate', 'duplicate')
            ->where('duplicate.soundex = :soundex')
            ->setParameter('soundex', $soundex)
            ->getQuery()
            ->useResultCache(true)
            ->useQueryCache(true)
            ->getResult();
    }

    /**
     * @inheritDoc
     */
    public function findExactMatches(string $className): array
    {
        return $this->createQueryBuilder('o')
            ->innerJoin('o.duplicate', 'duplicate')
            ->where('duplicate.className = :className')
            ->setParameter('className', $className)
            ->groupBy('o.duplicate')
            ->having('count(o.id) > 1')
            ->orderBy('count(o.id)')
            ->getQuery()
            ->useResultCache(true)
            ->useQueryCache(true)
            ->getResult();
    }

     /**
     * @inheritDoc
     */
    public function findByDuplicate(DuplicateInterface $duplicate): array
    {
        return $this->createQueryBuilder('o')
            ->where('o.duplicate = :duplicate')
            ->setParameter('duplicate', $duplicate)
            ->getQuery()
            ->useResultCache(true)
            ->useQueryCache(true)
            ->getResult();
    }
}
