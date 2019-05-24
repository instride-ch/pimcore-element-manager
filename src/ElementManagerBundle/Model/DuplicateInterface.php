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

namespace Wvision\Bundle\ElementManagerBundle\Model;

use CoreShop\Component\Resource\Model\ResourceInterface;
use CoreShop\Component\Resource\Model\TimestampableInterface;
use Pimcore\Model\DataObject\Concrete;

interface DuplicateInterface extends ResourceInterface, TimestampableInterface
{
    /**
     * @return string
     */
    public function getClassName(): string;

    /**
     * @param string $className
     */
    public function setClassName(string $className): void;

    /**
     * @return string
     */
    public function getGroup(): string;

    /**
     * @param string $group
     */
    public function setGroup(string $group): void;

    /**
     * @return array
     */
    public function getData(): array;

    /**
     * @param array $data
     */
    public function setData(array $data): void;

    /**
     * @return string
     */
    public function getMd5(): string;

    /**
     * @param string $md5
     */
    public function setMd5(string $md5): void;

    /**
     * @return array
     */
    public function getFields(): array;

    /**
     * @param array $fields
     */
    public function setFields(array $fields): void;

    /**
     * @return int
     */
    public function getFieldsCrc(): int;

    /**
     * @param int $fieldsCrc
     */
    public function setFieldsCrc(int $fieldsCrc): void;

    /**
     * @return Concrete
     */
    public function getObject(): Concrete;

    /**
     * @param Concrete $object
     */
    public function setObject(Concrete $object): void;

    /**
     * @return string
     */
    public function getSoundex(): string;

    /**
     * @param string $soundex
     */
    public function setSoundex(string $soundex): void;

    /**
     * @return string
     */
    public function getMetaphone(): string;

    /**
     * @param string $metaphone
     */
    public function setMetaphone(string $metaphone): void;

    /**
     * @return DuplicateObjectInterface[]
     */
    public function getObjects(): array;
}
