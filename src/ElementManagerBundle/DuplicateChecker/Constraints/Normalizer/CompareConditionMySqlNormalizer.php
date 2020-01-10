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

namespace Wvision\Bundle\ElementManagerBundle\DuplicateChecker\Constraints\Normalizer;

use DateTime;
use Pimcore\Model\DataObject;
use Pimcore\Model\Element\ElementInterface;

class CompareConditionMySqlNormalizer
{
    public function addForStringFields(
        DataObject\Listing\Concrete $list,
        $field,
        $value,
        array $duplicateCheckTrimmedFields = []
    ): void {
        if (in_array($field, $duplicateCheckTrimmedFields, false)) {
            $list->addConditionParam($field . ' LIKE ?', mb_strtolower(trim($value), 'UTF-8'));
        } else {
            $list->addConditionParam('TRIM(LCASE(' . $field . ')) = ?', mb_strtolower(trim($value), 'UTF-8'));
        }
    }

    public function addForDateFields(DataObject\Listing\Concrete $list, $field, DateTime $value): void
    {
        $list->addConditionParam($field . ' = ?', $value->getTimestamp());
    }

    public function addForSingleRelationFields(DataObject\Listing\Concrete $list, $field, ElementInterface $value): void
    {
        $list->addConditionParam($field . '__id = ?', $value->getId());
    }

    public function addForMultiRelationFields(DataObject\Listing\Concrete $list, $field, $value): void
    {
        $ids = [];
        /** @var ElementInterface $row */
        foreach ($value as $row) {
            $ids[] = $row->getId();
        }

        $list->addConditionParam($field . ' = ?', implode(',', $ids) . ',');
    }
}
