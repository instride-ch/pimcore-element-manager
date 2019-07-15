<?php
/**
 * Element Manager.
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

namespace Wvision\Bundle\ElementManagerBundle\Model;

use CoreShop\Component\Resource\Model\AbstractResource;
use CoreShop\Component\Resource\Model\TimestampableTrait;

class PotentialDuplicate extends AbstractResource implements PotentialDuplicateInterface
{
    use TimestampableTrait;

    protected $id;

    /**
     * @var DuplicateObjectInterface
     */
    protected $duplicateFrom;

    /**
     * @var DuplicateObjectInterface
     */
    protected $duplicateTo;

    /**
     * @var bool
     */
    protected $declined = false;

    /**
     * {@inheritdoc}
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * {@inheritdoc}
     */
    public function getDuplicateFrom(): DuplicateObjectInterface
    {
        return $this->duplicateFrom;
    }

    /**
     * {@inheritdoc}
     */
    public function setDuplicateFrom(DuplicateObjectInterface $duplicateFrom): void
    {
        $this->duplicateFrom = $duplicateFrom;
    }

    /**
     * {@inheritdoc}
     */
    public function getDuplicateTo(): DuplicateObjectInterface
    {
        return $this->duplicateTo;
    }

    /**
     * {@inheritdoc}
     */
    public function setDuplicateTo(DuplicateObjectInterface $duplicateTo): void
    {
        $this->duplicateTo = $duplicateTo;
    }

    /**
     * {@inheritDoc}
     */
    public function getDeclined(): bool
    {
        return $this->declined;
    }

    /**
     * {@inheritDoc}
     */
    public function setDeclined(bool $declined): void
    {
        $this->declined = $declined;
    }
}
