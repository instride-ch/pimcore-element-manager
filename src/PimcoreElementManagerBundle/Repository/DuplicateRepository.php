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
use Pimcore\Model\DataObject\Concrete;
use Instride\Bundle\PimcoreElementManagerBundle\Model\DuplicateInterface;

class DuplicateRepository extends EntityRepository implements DuplicateRepositoryInterface
{
    /**
     * @inheritDoc
     */
    public function findForObject(Concrete $concrete): array
    {
        return $this->createQueryBuilder('o')
            ->andWhere('o.object = :object')
            ->setParameter('object', $concrete->getId())
            ->getQuery()
            ->enableResultCache()
            ->useQueryCache(true)
            ->getResult();
    }

    /**
     * @inheritDoc
     */
    public function findForMd5AndCrc(string $className, string $md5, int $crc): ?DuplicateInterface
    {
        return $this->createQueryBuilder('o')
            ->andWhere('o.md5 = :md5')
            ->andWhere('o.className = :className')
            ->andWhere('o.fieldsCrc = :crc')
            ->setParameter('md5', $md5)
            ->setParameter('crc', $crc)
            ->setParameter('className', $className)
            ->getQuery()
            ->enableResultCache()
            ->useQueryCache(true)
            ->getOneOrNullResult();
    }

    /**
     * @inheritDoc
     */
    public function findExactByAlgorithm(string $className, string $algorithm): array
    {
        return match ($algorithm) {
            'metaphone' => $this->findExactByMetaphone($className),
            'soundex' => $this->findExactBySoundex($className),
            default => throw new \InvalidArgumentException(\sprintf('Undefined algorithm %s', $algorithm)),
        };
    }

    public function findExactByMetaphone(string $className)
    {
        return $this->createQueryBuilder('m')
            ->where('m.metaphone IS NOT NULL and m.metaphone != \'\' AND m.className = :className')
            ->groupBy('m.metaphone')
            ->addGroupBy('m.className')
            ->having('count(m.id) > 1')
            ->setParameter('className', $className)
            ->getQuery()
            ->enableResultCache()
            ->useQueryCache(true)
            ->getResult();
    }

    public function findExactBySoundex(string $className)
    {
        return $this->createQueryBuilder('s')
            ->where('s.soundex IS NOT NULL and s.soundex != \'\' AND s.className = :className')
            ->groupBy('s.soundex')
            ->addGroupBy('s.className')
            ->having('count(s.id) > 1')
            ->setParameter('className', $className)
            ->getQuery()
            ->enableResultCache()
            ->useQueryCache(true)
            ->getResult();
    }

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
