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

namespace Instride\Bundle\PimcoreElementManagerBundle\SaveManager;

use Instride\Bundle\PimcoreElementManagerBundle\DuplicateChecker\DuplicateServiceInterface;
use Instride\Bundle\PimcoreElementManagerBundle\Exception\DuplicatesException;
use Pimcore\Model\DataObject\Concrete;

class DuplicationSaveHandler extends AbstractObjectSaveHandler
{
    public function __construct(private readonly DuplicateServiceInterface $duplicateService) {}

    /**
     * @throws DuplicatesException
     */
    public function preSave(Concrete $object, array $options): void
    {
        $result = $this->duplicateService->findDuplicates($object, $options['group'] ? [$options['group']] : null);

        if (\count($result) > 0) {
            $duplicatesException = new DuplicatesException(\sprintf('Duplicates of Object %s found', $object->getClassName()));
            $duplicatesException->setDuplicates($result);

            throw $duplicatesException;
        }
    }
}
