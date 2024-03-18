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

class FieldMetadata implements FieldMetadataInterface
{
    public function __construct(private readonly string $name, private readonly array $config) {}

    public function getName(): string
    {
        return $this->name;
    }

    public function hasConfig(string $name): bool
    {
        return \array_key_exists($name, $this->config);
    }

    public function getConfig(string $name): mixed
    {
        if (!$this->hasConfig($name)) {
            throw new \InvalidArgumentException(\sprintf('Key %s not found in config', $name));
        }

        return $this->config[$name];
    }

    public function getSimilarityIdentifier(): ?string
    {
        return $this->hasConfig('similarity') ? $this->getConfig('similarity') : null;
    }

    public function getTransformerIdentifier(): ?string
    {
        return $this->hasConfig('transformer') ? $this->getConfig('transformer') : null;
    }

    public function getMetaphone(): ?bool
    {
        return $this->hasConfig('metaphone') ? $this->getConfig('metaphone') : null;
    }

    public function getSoundex(): ?bool
    {
        return $this->hasConfig('soundex') ? $this->getConfig('soundex') : null;
    }
}
