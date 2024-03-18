<?php

declare(strict_types=1);

/**
 * Pimcore Element Manager.
 *
 * LICENSE
 *
 * This source file is subject to the GNU General Public License version 3 (GPLv3)
 * For the full copyright and license information, please view the LICENSE.md and gpl-3.0.txt
 * files that are distributed with this source code.
 *
 * @copyright 2024 instride AG (https://instride.ch)
 * @license   https://github.com/instride-ch/pimcore-element-manager/blob/main/gpl-3.0.txt GNU General Public License version 3 (GPLv3)
 */

namespace Instride\Bundle\PimcoreElementManagerBundle\Repository;

use CoreShop\Bundle\ResourceBundle\Doctrine\ORM\EntityRepository;
use Instride\Bundle\PimcoreElementManagerBundle\Model\DuplicateInterface;
use Pimcore\Model\DataObject\Concrete;

class DuplicateObjectRepository extends EntityRepository implements DuplicateObjectRepositoryInterface
{
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
    public function findByDuplicateAndAlgorithmValue(int $currentId, string $algorithm, string $value): array
    {
        switch ($algorithm) {
            case 'metaphone':
                return $this->findByDuplicateAndMetaphone($currentId, $value);
            case 'soundex':
                return $this->findByDuplicateAndSoundex($currentId, $value);
        }

        throw new \InvalidArgumentException(\sprintf('Undefined algorithm %s', $algorithm));
    }

    public function findByDuplicateAndMetaphone(int $currentId, string $metaphone): array
    {
        return $this->createQueryBuilder('o')
            ->innerJoin('o.duplicate', 'duplicate')
            ->where('duplicate.metaphone = :metaphone')
            ->andWhere('duplicate.object <> :objectId')
            ->setParameter('objectId', $currentId)
            ->setParameter('metaphone', $metaphone)
            ->getQuery()
            ->enableResultCache()
            ->useQueryCache(true)
            ->getResult();
    }

    public function findByDuplicateAndSoundex(int $currentId, string $soundex): array
    {
        return $this->createQueryBuilder('o')
            ->innerJoin('o.duplicate', 'duplicate')
            ->where('duplicate.soundex = :soundex')
            ->andWhere('duplicate.object <> :objectId')
            ->setParameter('objectId', $currentId)
            ->setParameter('soundex', $soundex)
            ->getQuery()
            ->enableResultCache()
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
            ->enableResultCache()
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
            ->enableResultCache()
            ->useQueryCache(true)
            ->getResult();
    }
}
