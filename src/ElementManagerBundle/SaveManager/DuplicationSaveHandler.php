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

use Wvision\Bundle\ElementManagerBundle\DuplicateChecker\DuplicateServiceInterface;
use Pimcore\Model\DataObject\Concrete;
use Wvision\Bundle\ElementManagerBundle\Exception\DuplicatesException;

final class DuplicationSaveHandler extends AbstractObjectSaveHandler
{
    /**
     * @var DuplicateServiceInterface
     */
    private $duplicateService;

    /**
     * @param DuplicateServiceInterface $duplicateService
     */
    public function __construct(DuplicateServiceInterface $duplicateService)
    {
        $this->duplicateService = $duplicateService;
    }

    /**
     * {@inheritdoc}
     *
     * @throws DuplicatesException
     */
    public function preSave(Concrete $object, array $options): void
    {
        $result = $this->duplicateService->findDuplicates($object, $options['group'] ? [$options['group']] : null);

        if (count($result) > 0) {
            $duplicatesException = new DuplicatesException(sprintf('Duplicates of Object %s found', $object->getClassName()));
            $duplicatesException->setDuplicates($result);

            throw $duplicatesException;
        }
    }
}
