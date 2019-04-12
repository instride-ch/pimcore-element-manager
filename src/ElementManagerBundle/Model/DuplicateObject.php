<?php
/**
 * Element Manager
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

namespace ElementManagerBundle\Model;

use CoreShop\Component\Resource\Model\AbstractResource;
use CoreShop\Component\Resource\Model\TimestampableTrait;
use Pimcore\Model\DataObject\Concrete;

class DuplicateObject extends AbstractResource implements DuplicateObjectInterface
{
    use TimestampableTrait;

    /**
     * @var int
     */
    protected $id;

    /**
     * @var Concrete
     */
    protected $object;

    /**
     * @var DuplicateInterface
     */
    protected $duplicate;

    /**
     * @inheritDoc
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @inheritDoc
     */
    public function getObject(): Concrete
    {
        return $this->object;
    }

    /**
     * @inheritDoc
     */
    public function setObject(Concrete $object): void
    {
        $this->object = $object;
    }

    /**
     * @inheritDoc
     */
    public function getDuplicate(): DuplicateInterface
    {
        return $this->duplicate;
    }

    /**
     * @inheritDoc
     */
    public function setDuplicate(DuplicateInterface $duplicate): void
    {
        $this->duplicate = $duplicate;
    }
}
