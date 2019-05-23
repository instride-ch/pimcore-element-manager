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

namespace WVision\Bundle\ElementManagerBundle\Model;

use CoreShop\Component\Resource\Model\AbstractResource;
use CoreShop\Component\Resource\Model\TimestampableTrait;
use Doctrine\Common\Collections\ArrayCollection;
use Pimcore\Model\DataObject\Concrete;

class Duplicate extends AbstractResource implements DuplicateInterface
{
    use TimestampableTrait;

    /**
     * @var int
     */
    protected $id;

    /**
     * @var string
     */
    protected $className;

    /**
     * @var string
     */
    protected $group;

    /**
     * @var array
     */
    protected $data;

    /**
     * @var string
     */
    protected $md5;

    /**
     * @var array
     */
    protected $fields;

    /**
     * @var int
     */
    protected $fieldsCrc;

    /**
     * @var Concrete
     */
    protected $object;

    /**
     * @var string
     */
    protected $metaphone;

    /**
     * @var string
     */
    protected $soundex;

    /**
     * @var array
     */
    protected $objects;

    /**
     * Duplicate constructor.
     */
    public function __construct()
    {
        $this->objects = new ArrayCollection();
    }

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
    public function getClassName(): string
    {
        return $this->className;
    }

    /**
     * @inheritDoc
     */
    public function setClassName(string $className): void
    {
        $this->className = $className;
    }

    /**
     * @inheritDoc
     */
    public function getGroup(): string
    {
        return $this->group;
    }

    /**
     * @inheritDoc
     */
    public function setGroup(string $group): void
    {
        $this->group = $group;
    }

    /**
     * @inheritDoc
     */
    public function getData(): array
    {
        return $this->data;
    }

    /**
     * @inheritDoc
     */
    public function setData(array $data): void
    {
        $this->data = $data;
    }

    /**
     * @inheritDoc
     */
    public function getMd5(): string
    {
        return $this->md5;
    }

    /**
     * @inheritDoc
     */
    public function setMd5(string $md5): void
    {
        $this->md5 = $md5;
    }

    /**
     * @inheritDoc
     */
    public function getFields(): array
    {
        return $this->fields;
    }

    /**
     * @inheritDoc
     */
    public function setFields(array $fields): void
    {
        $this->fields = $fields;
    }

    /**
     * @inheritDoc
     */
    public function getFieldsCrc(): int
    {
        return $this->fieldsCrc;
    }

    /**
     * @inheritDoc
     */
    public function setFieldsCrc(int $fieldsCrc): void
    {
        $this->fieldsCrc = $fieldsCrc;
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
    public function getMetaphone(): string
    {
        return $this->metaphone;
    }

    /**
     * @inheritDoc
     */
    public function setMetaphone(string $metaphone): void
    {
        $this->metaphone = $metaphone;
    }

    /**
     * @inheritDoc
     */
    public function getSoundex(): string
    {
        return $this->soundex;
    }

    /**
     * @inheritDoc
     */
    public function setSoundex(string $soundex): void
    {
        $this->soundex = $soundex;
    }

    /**
     * @inheritDoc
     */
    public function getObjects(): array
    {
        return $this->objects;
    }
}
