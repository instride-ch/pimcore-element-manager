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

interface FieldMetadataInterface
{
    public function getName(): string;

    public function hasConfig(string $name): bool;

    public function getConfig(string $name): mixed;

    public function getSimilarityIdentifier(): ?string;

    public function getTransformerIdentifier(): ?string;

    public function getSoundex(): ?bool;

    public function getMetaphone(): ?bool;
}
