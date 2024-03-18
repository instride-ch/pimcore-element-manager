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
use Instride\Bundle\PimcoreElementManagerBundle\Model\DuplicateInterface;
use Pimcore\Model\DataObject\Concrete;

interface DuplicateRepositoryInterface extends RepositoryInterface
{
    /**
     * @return DuplicateInterface[]
     */
    public function findForObject(Concrete $concrete): array;

    /**
     * @return DuplicateInterface[]
     */
    public function findExactByAlgorithm(string $className, string $algorithm): array;

    /**
     * @throws NonUniqueResultException
     */
    public function findForMd5AndCrc(string $className, string $md5, int $crc): ?DuplicateInterface;

    public function deleteForObject(Concrete $concrete);
}
