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

class Metadata implements MetadataInterface
{
    /**
     * @var string
     */
    private $className;

    /**
     * @var GroupMetadataInterface[]
     */
    private $groups;

    /**
     * @var string[]
     */
    private $listFields;

    /**
     * @param string                   $className
     * @param GroupMetadataInterface[] $groups
     * @param string[]                 $listFields
     */
    public function __construct(string $className, array $groups, array $listFields)
    {
        $this->className = $className;
        $this->groups = $groups;
        $this->listFields = $listFields;
    }

    /**
     * {@inheritdoc}
     */
    public function getClassName(): string
    {
        return $this->className;
    }

    /**
     * {@inheritdoc}
     */
    public function setClassName(string $className): void
    {
        $this->className = $className;
    }

    /**
     * {@inheritdoc}
     */
    public function getGroups(): array
    {
        return $this->groups;
    }

    /**
     * {@inheritdoc}
     */
    public function setGroups(array $groups): void
    {
        $this->groups = $groups;
    }

    /**
     * {@inheritdoc}
     */
    public function getListFields(): array
    {
        return $this->listFields;
    }

    /**
     * {@inheritdoc}
     */
    public function setListFields(array $listFields): void
    {
        $this->listFields = $listFields;
    }

    /**
     * {@inheritdoc}
     */
    public function getGroup(string $name): ?GroupMetadataInterface
    {
        $filteredGroups = array_filter($this->groups,
            static function (GroupMetadataInterface $groupMetadata) use ($name) {
                return $groupMetadata->getName() === $name;
            });

        return reset($filteredGroups);
    }
}
