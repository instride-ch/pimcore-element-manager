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
use Instride\Bundle\PimcoreElementManagerBundle\Model\DuplicateInterface;
use Instride\Bundle\PimcoreElementManagerBundle\Model\DuplicateObjectInterface;
use Pimcore\Model\DataObject\Concrete;

interface DuplicateObjectRepositoryInterface extends RepositoryInterface
{
    public function deleteForObject(Concrete $concrete);

    /**
     * @return DuplicateObjectInterface[]
     */
    public function findExactMatches(string $className): array;

    /**
     * @return DuplicateObjectInterface[]
     */
    public function findByDuplicateAndAlgorithmValue(int $currentId, string $algorithm, string $value): array;

    /**
     * @return DuplicateObjectInterface[]
     */
    public function findByDuplicate(DuplicateInterface $duplicate): array;
}
