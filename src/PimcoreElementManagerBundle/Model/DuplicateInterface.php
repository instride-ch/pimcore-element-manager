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
use Pimcore\Model\DataObject\Concrete;

interface DuplicateInterface extends ResourceInterface, TimestampableInterface
{
    public function getClassName(): string;

    public function setClassName(string $className): void;

    public function getGroup(): string;

    public function setGroup(string $group): void;

    public function getData(): array;

    public function setData(array $data): void;

    public function getMd5(): string;

    public function setMd5(string $md5): void;

    public function getFields(): array;

    public function setFields(array $fields): void;

    public function getFieldsCrc(): int;

    public function setFieldsCrc(int $fieldsCrc): void;

    public function getObject(): Concrete;

    public function setObject(Concrete $object): void;

    public function getSoundex(): ?string;

    public function setSoundex(?string $soundex): void;

    public function getMetaphone(): ?string;

    public function setMetaphone(?string $metaphone): void;

    /**
     * @return DuplicateObjectInterface[]
     */
    public function getObjects(): array;
}
