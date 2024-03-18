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

use CoreShop\Component\Resource\Model\AbstractResource;
use CoreShop\Component\Resource\Model\TimestampableTrait;

class PotentialDuplicate extends AbstractResource implements PotentialDuplicateInterface
{
    use TimestampableTrait;

    protected int $id;
    protected DuplicateObjectInterface $duplicateFrom;
    protected DuplicateObjectInterface $duplicateTo;
    protected bool $declined = false;

    /**
     * @inheritDoc
     */
    public function getId(): int
    {
        return $this->id;
    }

    public function getDuplicateFrom(): DuplicateObjectInterface
    {
        return $this->duplicateFrom;
    }

    public function setDuplicateFrom(DuplicateObjectInterface $duplicateFrom): void
    {
        $this->duplicateFrom = $duplicateFrom;
    }

    public function getDuplicateTo(): DuplicateObjectInterface
    {
        return $this->duplicateTo;
    }

    public function setDuplicateTo(DuplicateObjectInterface $duplicateTo): void
    {
        $this->duplicateTo = $duplicateTo;
    }

    public function getDeclined(): bool
    {
        return $this->declined;
    }

    public function setDeclined(bool $declined): void
    {
        $this->declined = $declined;
    }
}
