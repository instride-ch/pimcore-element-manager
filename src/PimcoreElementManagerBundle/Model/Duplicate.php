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
use Doctrine\Common\Collections\ArrayCollection;
use Pimcore\Model\DataObject\Concrete;

class Duplicate extends AbstractResource implements DuplicateInterface
{
    use TimestampableTrait;

    protected int $id;
    protected string $className;
    protected string $group;
    protected array $data;
    protected string $md5;
    protected array $fields;
    protected int $fieldsCrc;
    protected Concrete $object;
    protected string $metaphone;
    protected string $soundex;
    protected array|ArrayCollection $objects;

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

    public function getClassName(): string
    {
        return $this->className;
    }

    public function setClassName(string $className): void
    {
        $this->className = $className;
    }

    public function getGroup(): string
    {
        return $this->group;
    }

    public function setGroup(string $group): void
    {
        $this->group = $group;
    }

    public function getData(): array
    {
        return $this->data;
    }

    public function setData(array $data): void
    {
        $this->data = $data;
    }

    public function getMd5(): string
    {
        return $this->md5;
    }

    public function setMd5(string $md5): void
    {
        $this->md5 = $md5;
    }

    public function getFields(): array
    {
        return $this->fields;
    }

    public function setFields(array $fields): void
    {
        $this->fields = $fields;
    }

    public function getFieldsCrc(): int
    {
        return $this->fieldsCrc;
    }

    public function setFieldsCrc(int $fieldsCrc): void
    {
        $this->fieldsCrc = $fieldsCrc;
    }

    public function getObject(): Concrete
    {
        return $this->object;
    }

    public function setObject(Concrete $object): void
    {
        $this->object = $object;
    }

    public function getMetaphone(): ?string
    {
        return $this->metaphone;
    }

    public function setMetaphone(?string $metaphone): void
    {
        $this->metaphone = $metaphone;
    }

    public function getSoundex(): ?string
    {
        return $this->soundex;
    }

    public function setSoundex(?string $soundex): void
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
