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

namespace WVision\Bundle\ElementManagerBundle\SaveManager;

use Pimcore\Model\DataObject\Concrete;

class ObjectSaveManagers
{
    private $saveManagers = [];

    /**
     * @param Concrete $concrete
     * @return bool
     */
    public function hasSaveManager(Concrete $concrete)
    {
        return array_key_exists($concrete->getClassName(), $this->saveManagers);
    }

    /**
     * @param Concrete $concrete
     * @return ObjectSaveManagerInterface
     */
    public function getSaveManger(Concrete $concrete)
    {
        if (!$this->hasSaveManager($concrete)) {
            throw new \InvalidArgumentException(sprintf('No Save Manager for Class %s found',
                $concrete->getClassName()));
        }

        return $this->saveManagers[$concrete->getClassName()];
    }

    /**
     * @param                            $class
     * @param ObjectSaveManagerInterface $saveManager
     */
    public function addSaveManager($class, ObjectSaveManagerInterface $saveManager)
    {
        $this->saveManagers[$class] = $saveManager;
    }
}
