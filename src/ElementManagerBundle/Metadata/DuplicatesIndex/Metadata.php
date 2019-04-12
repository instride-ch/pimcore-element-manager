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

class Metadata implements MetadataInterface
{
    /**
     * @var string
     */
    private $className;

    /**
     * @var GroupMetadataInterface[]
     */
    private $groups = [];

    /**
     * @param string                   $className
     * @param GroupMetadataInterface[] $groups
     */
    public function __construct(string $className, array $groups)
    {
        $this->className = $className;
        $this->groups = $groups;
    }

    /**
     * {@inheritDoc}
     */
    public function getClassName(): string
    {
        return $this->className;
    }

    /**
     * {@inheritDoc}
     */
    public function setClassName(string $className): void
    {
        $this->className = $className;
    }

    /**
     * {@inheritDoc}
     */
    public function getGroups(): array
    {
        return $this->groups;
    }

    /**
     * {@inheritDoc}
     */
    public function setGroups(array $groups): void
    {
        $this->groups = $groups;
    }

    /**
     * {@inheritDoc}
     */
    public function getGroup(string $name): ?GroupMetadataInterface
    {
        return reset(array_filter($this->groups, function(GroupMetadataInterface $groupMetadata) use ($name) {
            return $groupMetadata->getName() === $name;
        }));
    }
}
