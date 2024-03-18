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

namespace Instride\Bundle\PimcoreElementManagerBundle\DuplicateIndex;

use CoreShop\Component\Resource\Factory\FactoryInterface;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\NonUniqueResultException;
use Instride\Bundle\PimcoreElementManagerBundle\DuplicateIndex\DataTransformer\DataTransformerFactoryInterface;
use Instride\Bundle\PimcoreElementManagerBundle\Metadata\DuplicatesIndex\FieldMetadataInterface;
use Instride\Bundle\PimcoreElementManagerBundle\Metadata\DuplicatesIndex\GroupMetadataInterface;
use Instride\Bundle\PimcoreElementManagerBundle\Metadata\DuplicatesIndex\MetadataInterface;
use Instride\Bundle\PimcoreElementManagerBundle\Model\DuplicateInterface;
use Instride\Bundle\PimcoreElementManagerBundle\Model\DuplicateObjectInterface;
use Instride\Bundle\PimcoreElementManagerBundle\Repository\DuplicateObjectRepositoryInterface;
use Instride\Bundle\PimcoreElementManagerBundle\Repository\DuplicateRepositoryInterface;
use Pimcore\Model\DataObject\Concrete;
use Symfony\Component\PropertyAccess\PropertyAccess;

class DuplicatesIndexWorker implements DuplicatesIndexWorkerInterface
{
    public function __construct(
        private readonly DataTransformerFactoryInterface $dataTransformerFactory,
        private readonly EntityManagerInterface $entityManager,
        private readonly DuplicateRepositoryInterface $duplicateRepository,
        private readonly DuplicateObjectRepositoryInterface $duplicateObjectRepository,
        private readonly FactoryInterface $duplicateFactory,
        private readonly FactoryInterface $duplicateObjectFactory
    ) {}

    /**
     * @inheritDoc
     */
    public function updateIndex(MetadataInterface $metadata, Concrete $concrete): void
    {
        $accessor = PropertyAccess::createPropertyAccessor();

        $duplicateDataRows = [];

        foreach ($metadata->getGroups() as $group) {
            $data = [];

            foreach ($group->getFields() as $field) {
                $value = $accessor->getValue($concrete, $field->getName());

                $data[$field->getName()] = $this->transformData($value, $field);
            }

            $duplicateDataRows[$group->getName()] = $data;
        }

        $this->updateDuplicateIndex($metadata, $concrete, $duplicateDataRows);
    }

    /**
     * @throws NonUniqueResultException
     * @throws \JsonException
     */
    protected function updateDuplicateIndex(MetadataInterface $metadata, Concrete $concrete, array $duplicateDataRows): void
    {
        $this->duplicateRepository->deleteForObject($concrete);
        $this->duplicateObjectRepository->deleteForObject($concrete);

        foreach ($duplicateDataRows as $group => $duplicateDataRow) {
            $metadataGroup = $metadata->getGroup($group);
            $fieldCombination = null !== $metadataGroup ? $metadataGroup->getFieldKeys() : [];

            $dataMd5 = \md5(\json_encode($duplicateDataRow, JSON_THROW_ON_ERROR));
            $fieldCombinationCrc = \crc32(\implode(',', $fieldCombination));

            $duplicate = $this->duplicateRepository->findForMd5AndCrc($metadata->getClassName(), $dataMd5, $fieldCombinationCrc);

            if (!$duplicate) {
                /** @var DuplicateInterface $duplicate */
                $duplicate = $this->duplicateFactory->createNew();
                $duplicate->setClassName($metadata->getClassName());
                $duplicate->setGroup($group);
                $duplicate->setData($duplicateDataRow);
                $duplicate->setMd5($dataMd5);
                $duplicate->setFields($fieldCombination);
                $duplicate->setFieldsCrc($fieldCombinationCrc);
                $duplicate->setSoundex(
                    $this->calculateSoundData('soundex', $duplicateDataRow, $metadata->getGroup($group))
                );
                $duplicate->setMetaphone(
                    $this->calculateSoundData('metaphone', $duplicateDataRow, $metadata->getGroup($group))
                );
                $duplicate->setObject($concrete);

                $this->entityManager->persist($duplicate);
            }

            /** @var DuplicateObjectInterface $duplicateObject */
            $duplicateObject = $this->duplicateObjectFactory->createNew();
            $duplicateObject->setDuplicate($duplicate);
            $duplicateObject->setObject($concrete);

            $this->entityManager->persist($duplicateObject);
        }

        $this->entityManager->flush();
    }

    protected function calculateSoundData(
        string $algorithm,
        array $duplicateData,
        GroupMetadataInterface $groupMetadata
    ): ?string {
        $data = [];

        foreach ($groupMetadata->getFields() as $field) {
            if ($field->hasConfig($algorithm) && $field->getConfig($algorithm)) {
                $data[] = $duplicateData[$field->getName()];
            }
        }

        if (\count($data) === 0) {
            return null;
        }

        foreach ($data as $key => $value) {
            if ($algorithm === 'soundex') {
                $data[$key] = \soundex($value);
            } elseif ($algorithm === 'metaphone') {
                $data[$key] = \metaphone($value);
            }
        }

        return \implode('', $data);
    }

    protected function transformData($value, FieldMetadataInterface $field): mixed
    {
        if ($field->getTransformerIdentifier()) {
            return $this->dataTransformerFactory->getInstance($field->getTransformerIdentifier())->transform($value);
        }

        return $value;
    }

    protected function isRelevantForIndex(Concrete $concrete): bool
    {
        return $concrete->getPublished();
    }
}
