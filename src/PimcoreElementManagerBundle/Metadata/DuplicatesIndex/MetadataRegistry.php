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

class MetadataRegistry implements MetadataRegistryInterface
{
    protected array $metadata = [];
    protected array $metadataClassAlias = [];

    /**
     * @inheritDoc
     */
    public function all(): array
    {
        return $this->metadata;
    }

    public function has(string $className): bool
    {
        return \array_key_exists($className, $this->metadataClassAlias);
    }

    public function get(string $className): MetadataInterface
    {
        if (!$this->has($className)) {
            throw new \InvalidArgumentException(\sprintf('Class %s not found', $className));
        }

        return $this->metadataClassAlias[$className];
    }

    public function register(MetadataInterface $metadata): void
    {
        $this->metadata[] = $metadata;
        $this->metadataClassAlias[$metadata->getClassName()] = $metadata;
    }
}
