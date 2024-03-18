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

use CoreShop\Component\Resource\Repository\RepositoryInterface;
use Doctrine\ORM\NonUniqueResultException;
use Instride\Bundle\PimcoreElementManagerBundle\Model\DuplicateObjectInterface;
use Instride\Bundle\PimcoreElementManagerBundle\Model\PotentialDuplicateInterface;

interface PotentialDuplicateRepositoryInterface extends RepositoryInterface
{
    public function deleteAll();

    public function deleteForClass(string $className);

    /**
     * @return PotentialDuplicateInterface[]
     */
    public function findForClassName(string $className, bool $declined, int $offset, int $limit): array;

    public function findCountForClassName(string $className, bool $declined): int;

    /**
     * @throws NonUniqueResultException
     */
    public function findDuplication(
        DuplicateObjectInterface $duplicateObject1,
        DuplicateObjectInterface $duplicateObject2
    ): ?PotentialDuplicateInterface;
}
