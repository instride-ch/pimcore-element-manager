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

namespace Wvision\Bundle\ElementManagerBundle\Metadata\DuplicatesIndex;

interface FieldMetadataInterface
{
    /**
     * @return string
     */
    public function getName(): string;

    /**
     * @param string $name
     * @return bool
     */
    public function hasConfig(string $name): bool;

    /**
     * @param string $name
     * @return mixed
     */
    public function getConfig(string $name);

    /**
     * @return string
     */
    public function getSimilarityIdentifier(): ?string;

    /**
     * @return string
     */
    public function getTransformerIdentifier(): ?string;

    /**
     * @return bool
     */
    public function getSoundex(): bool;

    /**
     * @return bool
     */
    public function getMetaphone(): bool;
}
