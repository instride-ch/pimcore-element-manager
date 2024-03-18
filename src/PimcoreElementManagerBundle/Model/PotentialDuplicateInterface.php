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

namespace Instride\Bundle\PimcoreElementManagerBundle\Model;

use CoreShop\Component\Resource\Model\ResourceInterface;
use CoreShop\Component\Resource\Model\TimestampableInterface;

interface PotentialDuplicateInterface extends ResourceInterface, TimestampableInterface
{
    public function getDuplicateFrom(): DuplicateObjectInterface;

    public function setDuplicateFrom(DuplicateObjectInterface $duplicateFrom): void;

    public function getDuplicateTo(): DuplicateObjectInterface;

    public function setDuplicateTo(DuplicateObjectInterface $duplicateTo): void;

    public function getDeclined(): bool;

    public function setDeclined(bool $declined): void;
}