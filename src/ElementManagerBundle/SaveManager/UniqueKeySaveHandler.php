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

namespace Wvision\Bundle\ElementManagerBundle\SaveManager;

use Exception;
use Pimcore\Model\DataObject\Service;
use Pimcore\Model\DataObject\Concrete;

final class UniqueKeySaveHandler implements PostObjectSaveHandlerInterface
{
    use PostObjectSaveHandlerTrait;

    /**
     * {@inheritdoc}
     *
     * @throws Exception
     */
    public function preSave(Concrete $object, array $options): void
    {
        $object->setKey(Service::getUniqueKey($object));
    }
}
