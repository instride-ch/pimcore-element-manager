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

namespace Instride\Bundle\PimcoreElementManagerBundle\Exception;

use Pimcore\Model\Element\ElementInterface;
use Pimcore\Model\Element\ValidationException;

class DuplicatesException extends ValidationException
{
    /**
     * @var ElementInterface[]
     */
    private array $duplicates;

    /**
     * @return ElementInterface[]
     */
    public function getDuplicates(): array
    {
        return $this->duplicates;
    }

    /**
     * @param ElementInterface[] $duplicates
     */
    public function setDuplicates(array $duplicates): void
    {
        $this->duplicates = $duplicates;
    }
}
