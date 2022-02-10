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
 * @copyright  Copyright (c) 2016-2022 w-vision AG (https://www.w-vision.ch)
 * @license    https://github.com/w-vision/ImportDefinitions/blob/master/gpl-3.0.txt GNU General Public License version 3 (GPLv3)
 */

namespace Wvision\Bundle\ElementManagerBundle\Metadata\DuplicatesIndex;

interface GroupMetadataInterface
{
    /**
     * @return string
     */
    public function getName(): string;

    /**
     * @return FieldMetadataInterface[]
     */
    public function getFields(): array;

    /**
     * @param string $name
     *
     * @return FieldMetadataInterface|null
     */
    public function getField(string $name): ?FieldMetadataInterface;

    /**
     * @return array
     */
    public function getFieldKeys(): array;
}
