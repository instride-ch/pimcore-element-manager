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
use Pimcore\Model\DataObject\Concrete;

class DuplicateObject extends AbstractResource implements DuplicateObjectInterface
{
    use TimestampableTrait;

    protected int $id;
    protected Concrete $object;
    protected DuplicateInterface $duplicate;

    /**
     * @inheritDoc
     */
    public function getId(): int
    {
        return $this->id;
    }

    public function getObject(): Concrete
    {
        return $this->object;
    }

    public function setObject(Concrete $object): void
    {
        $this->object = $object;
    }

    public function getDuplicate(): DuplicateInterface
    {
        return $this->duplicate;
    }

    public function setDuplicate(DuplicateInterface $duplicate): void
    {
        $this->duplicate = $duplicate;
    }
}
