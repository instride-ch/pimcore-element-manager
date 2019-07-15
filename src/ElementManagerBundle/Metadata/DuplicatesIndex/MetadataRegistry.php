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
 * @copyright  Copyright (c) 2016-2018 w-vision AG (https://www.w-vision.ch)
 * @license    https://github.com/w-vision/ImportDefinitions/blob/master/gpl-3.0.txt GNU General Public License version 3 (GPLv3)
 */

namespace Wvision\Bundle\ElementManagerBundle\Metadata\DuplicatesIndex;

use InvalidArgumentException;

class MetadataRegistry implements MetadataRegistryInterface
{
    /**
     * @var array
     */
    protected $metadata = [];

    /**
     * @var array
     */
    protected $metadataClassAlias = [];

    /**
     * {@inheritdoc}
     */
    public function all(): array
    {
        return $this->metadata;
    }

    /**
     * {@inheritdoc}
     */
    public function has(string $className): bool
    {
        return array_key_exists($className, $this->metadataClassAlias);
    }

    /**
     * {@inheritdoc}
     */
    public function get(string $className): MetadataInterface
    {
        if (!$this->has($className)) {
            throw new InvalidArgumentException(sprintf('Class %s not found', $className));
        }

        return $this->metadataClassAlias[$className];
    }

    /**
     * {@inheritdoc}
     */
    public function register(MetadataInterface $metadata): void
    {
        $this->metadata[] = $metadata;
        $this->metadataClassAlias[$metadata->getClassName()] = $metadata;
    }
}
