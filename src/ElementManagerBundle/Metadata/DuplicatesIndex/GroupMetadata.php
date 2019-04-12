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

namespace ElementManagerBundle\Metadata\DuplicatesIndex;

class GroupMetadata implements GroupMetadataInterface
{
    /**
     * @var string
     */
    private $name;

    /**
     * @var FieldMetadataInterface[]
     */
    private $fields = [];

    /**
     * @param string                   $name
     * @param FieldMetadataInterface[] $fields
     */
    public function __construct(string $name, array $fields)
    {
        $this->name = $name;
        $this->fields = $fields;
    }

    /**
     * {@inheritDoc}
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * {@inheritDoc}
     */
    public function setName(string $name): void
    {
        $this->name = $name;
    }

    /**
     * {@inheritDoc}
     */
    public function getFields(): array
    {
        return $this->fields;
    }

    /**
     * {@inheritDoc}
     */
    public function setFields(array $fields): void
    {
        $this->fields = $fields;
    }

    /**
     * {@inheritDoc}
     */
    public function getField(string $name): ?FieldMetadataInterface
    {
        return reset(array_filter($this->fields, function(FieldMetadataInterface $fieldMetadata) use ($name) {
            return $fieldMetadata->getName() === $name;
        }));
    }

    /**
     * {@inheritDoc}
     */
    public function getFieldKeys(): array
    {
        return array_map(function(FieldMetadataInterface $fieldMetadata) {
            return $fieldMetadata->getName();
        }, $this->fields);
    }
}
