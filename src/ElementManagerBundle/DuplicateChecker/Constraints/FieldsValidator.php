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

namespace Wvision\Bundle\ElementManagerBundle\DuplicateChecker\Constraints;

use CoreShop\Component\Resource\Exception\UnexpectedTypeException;
use DateTime;
use Exception;
use InvalidArgumentException;
use Wvision\Bundle\ElementManagerBundle\DuplicateChecker\Constraints\Normalizer\CompareConditionMySqlNormalizer;
use Symfony\Component\Validator\Constraint;
use Pimcore\Model\DataObject;
use Pimcore\Model\Element\ElementInterface;

class FieldsValidator extends DuplicateConstraintValidator
{
    private $normalizer;

    public function __construct()
    {
        $this->normalizer = new CompareConditionMySqlNormalizer();
    }

    /**
     * @param mixed      $value
     * @param Constraint $constraint
     *
     * @throws Exception
     */
    public function validate($value, Constraint $constraint): void
    {
        if (!$constraint instanceof Fields) {
            throw new UnexpectedTypeException($constraint, __NAMESPACE__ . '\Fields');
        }

        $duplicates = $this->getDuplicatesByFields($value, $constraint->fields, $constraint->trim);

        if (null !== $duplicates && $duplicates->getTotalCount() > 0) {
            foreach ($duplicates->getObjects() as $duplicate) {
                $this->context->buildViolation($constraint->message)
                    ->setInvalidValue($duplicate)
                    ->addViolation();
            }
        }
    }

    /**
     * {@inheritdoc}
     *
     * @throws Exception
     */
    private function getDuplicatesByFields(
        DataObject\Concrete $address,
        array $fields,
        bool $trim = false,
        $limit = 0
    ): ?DataObject\Listing\Concrete {
        $data = [];
        foreach ($fields as $field) {
            $getter = 'get' . ucfirst($field);
            $value = $address->$getter();

            if (null === $value || '' === $value) {
                return null;
            }

            if ($trim) {
                $value = trim($value);
            }

            $data[$field] = $value;
        }

        $duplicates = $this->getDuplicatesByData($address::getList(), $data, $limit);

        if (null !== $duplicates && $address->getId()) {
            $duplicates->addConditionParam('o_id != ?', $address->getId());
        }

        return $duplicates;
    }

    /**
     * {@inheritdoc}
     *
     * @throws Exception
     */
    private function getDuplicatesByData(
        DataObject\Listing\Concrete $list,
        array $data,
        $limit = 0
    ): ?DataObject\Listing\Concrete {
        if (!count($data)) {
            return null;
        }

        $list->addConditionParam('o_published = ?', 1);

        foreach ($data as $field => $value) {
            if (null === $value || '' === $value) {
                return null;
            }

            $this->addNormalizedMysqlCompareCondition($list, $field, $value);
        }

        if ($limit) {
            $list->setLimit($limit);
        }

        return $list;
    }

    /**
     * @param DataObject\Listing\Concrete $list
     * @param $field
     * @param $value
     *
     * @throws Exception
     */
    private function addNormalizedMysqlCompareCondition(DataObject\Listing\Concrete $list, $field, $value): void
    {
        $class = DataObject\ClassDefinition::getById($list->getClassId());

        if (null === $class) {
            return;
        }

        /** @var DataObject\ClassDefinition\Data\QueryResourcePersistenceAwareInterface $fd */
        $fd = $class->getFieldDefinition($field);

        if (!$fd) {
            return;
        }

        if ($value instanceof ElementInterface) {
            $this->normalizer->addForSingleRelationFields($list, $field, $value);

            return;
        }

        if (is_array($value) && ($value[0] instanceof ElementInterface)) {
            $this->normalizer->addForMultiRelationFields($list, $field, $value);

            return;
        }

        if ($value instanceof DateTime) {
            $this->normalizer->addForDateFields($list, $field, $value);

            return;
        }

        if (strpos($fd->getQueryColumnType(), 'char') !== false) {
            $this->normalizer->addForStringFields($list, $field, $value);

            return;
        }

        $type = is_object($value) ? get_class($value) : gettype($value);

        throw new InvalidArgumentException(
            sprintf('Duplicate check for type of field %s not implemented (type of value: %s)', $field, $type)
        );
    }
}
