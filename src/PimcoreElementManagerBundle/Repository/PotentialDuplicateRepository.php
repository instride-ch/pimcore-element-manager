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
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Instride\Bundle\PimcoreElementManagerBundle\Model\DuplicateObjectInterface;
use Instride\Bundle\PimcoreElementManagerBundle\Model\PotentialDuplicateInterface;

class PotentialDuplicateRepository extends EntityRepository implements PotentialDuplicateRepositoryInterface
{
    public function deleteAll()
    {
        $query = $this->createQueryBuilder('o')
            ->delete()
            ->getQuery();

        return $query->execute();
    }

    public function deleteForClass(string $className)
    {
        $query = $this->createQueryBuilder('o')
            ->where('className = :className')
            ->setParameter('className', $className)
            ->delete()
            ->getQuery();

        return $query->execute();
    }

    public function findForClassName(string $className, bool $declined, int $offset, int $limit): array
    {
        return $this->createQueryBuilder('o')
            ->where('d.className = :className')
            ->andWhere('o.declined = :declined')
            ->innerJoin('o.duplicateFrom', 'f')
            ->innerJoin('f.duplicate', 'd')
            ->setParameter('className', $className)
            ->setParameter('declined', $declined)
            ->setFirstResult($limit * $offset)
            ->setMaxResults($limit)
            ->getQuery()
            ->enableResultCache()
            ->useQueryCache(true)
            ->getResult()
        ;
    }

    public function findCountForClassName(string $className, bool $declined): int
    {
        $query = $this->createQueryBuilder('o')
            ->where('d.className = :className')
            ->andWhere('o.declined = :declined')
            ->innerJoin('o.duplicateFrom', 'f')
            ->innerJoin('f.duplicate', 'd')
            ->setParameter('className', $className)
            ->setParameter('declined', $declined)
            ->getQuery();

        $paginator = new Paginator($query);

        return \count($paginator);
    }

    /**
     * @inheritDoc
     */
    public function findDuplication(DuplicateObjectInterface $duplicateObject1, DuplicateObjectInterface $duplicateObject2): ?PotentialDuplicateInterface
    {
        return $this->createQueryBuilder('o')
            ->andWhere('(o.duplicateFrom = :duplicate1 AND o.duplicateTo = :duplicate2)')
            ->orWhere('(o.duplicateFrom = :duplicate2 AND o.duplicateTo = :duplicate1)')
            ->setParameter('duplicate1', $duplicateObject1)
            ->setParameter('duplicate2', $duplicateObject2)
            ->getQuery()
            ->enableResultCache()
            ->useQueryCache(true)
            ->getOneOrNullResult();
    }
}
