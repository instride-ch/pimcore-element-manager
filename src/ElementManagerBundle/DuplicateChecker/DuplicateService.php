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
 * @copyright  Copyright (c) 2016-2020 w-vision AG (https://www.w-vision.ch)
 * @license    https://github.com/w-vision/ImportDefinitions/blob/master/gpl-3.0.txt GNU General Public License version 3 (GPLv3)
 */

namespace Wvision\Bundle\ElementManagerBundle\DuplicateChecker;

use Pimcore\Model\Element\ElementInterface;
use Symfony\Component\Validator\ConstraintViolationInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class DuplicateService implements DuplicateServiceInterface
{
    /**
     * @var ValidatorInterface
     */
    private $duplicateChecker;

    /**
     * @param ValidatorInterface $duplicateChecker
     */
    public function __construct(ValidatorInterface $duplicateChecker)
    {
        $this->duplicateChecker = $duplicateChecker;
    }

    /**
     * {@inheritdoc}
     */
    public function findDuplicates(ElementInterface $element, array $groups = null): array
    {
        $result = $this->duplicateChecker->validate($element, null, $groups);

        if ($result->count()) {
            return array_map(static function (ConstraintViolationInterface $result) {
                return $result->getInvalidValue();
            }, iterator_to_array($result));
        }

        return [];
    }
}
