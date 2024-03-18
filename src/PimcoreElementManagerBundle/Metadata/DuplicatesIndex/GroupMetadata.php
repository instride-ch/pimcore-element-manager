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

namespace Instride\Bundle\PimcoreElementManagerBundle\Metadata\DuplicatesIndex;

class GroupMetadata implements GroupMetadataInterface
{
    /**
     * @param FieldMetadataInterface[] $fields
     */
    public function __construct(private string $name, private array $fields) {}

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): void
    {
        $this->name = $name;
    }

    /**
     * @inheritDoc
     */
    public function getFields(): array
    {
        return $this->fields;
    }

    public function setFields(array $fields): void
    {
        $this->fields = $fields;
    }

    public function getField(string $name): ?FieldMetadataInterface
    {
        $filteredFields = \array_filter(
            $this->fields,
            static fn (FieldMetadataInterface $fieldMetadata) => $fieldMetadata->getName() === $name
        );

        return \reset($filteredFields);
    }

    public function getFieldKeys(): array
    {
        return \array_map(
            static fn (FieldMetadataInterface $fieldMetadata) => $fieldMetadata->getName(),
            $this->fields
        );
    }
}
