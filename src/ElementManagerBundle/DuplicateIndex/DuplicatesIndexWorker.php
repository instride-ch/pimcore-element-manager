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

namespace Wvision\Bundle\ElementManagerBundle\DuplicateIndex;

use CoreShop\Component\Resource\Factory\FactoryInterface;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\NonUniqueResultException;
use Wvision\Bundle\ElementManagerBundle\DuplicateIndex\DataTransformer\DataTransformerFactoryInterface;
use Wvision\Bundle\ElementManagerBundle\Metadata\DuplicatesIndex\FieldMetadataInterface;
use Wvision\Bundle\ElementManagerBundle\Metadata\DuplicatesIndex\GroupMetadataInterface;
use Wvision\Bundle\ElementManagerBundle\Metadata\DuplicatesIndex\MetadataInterface;
use Wvision\Bundle\ElementManagerBundle\Model\DuplicateInterface;
use Wvision\Bundle\ElementManagerBundle\Model\DuplicateObjectInterface;
use Wvision\Bundle\ElementManagerBundle\Repository\DuplicateObjectRepositoryInterface;
use Wvision\Bundle\ElementManagerBundle\Repository\DuplicateRepositoryInterface;
use Pimcore\Model\DataObject\Concrete;
use Symfony\Component\PropertyAccess\PropertyAccess;

class DuplicatesIndexWorker implements DuplicatesIndexWorkerInterface
{
    /**
     * @var DataTransformerFactoryInterface
     */
    private $dataTransformerFactory;

    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

    /**
     * @var DuplicateRepositoryInterface
     */
    private $duplicateRepository;

    /**
     * @var DuplicateObjectRepositoryInterface
     */
    private $duplicateObjectRepository;

    /**
     * @var FactoryInterface
     */
    private $duplicateFactory;

    /**
     * @var FactoryInterface
     */
    private $duplicateObjectFactory;

    /**
     * @param DataTransformerFactoryInterface    $dataTransformerFactory
     * @param EntityManagerInterface             $entityManager
     * @param DuplicateRepositoryInterface       $duplicateRepository
     * @param DuplicateObjectRepositoryInterface $duplicateObjectRepository
     * @param FactoryInterface                   $duplicateFactory
     * @param FactoryInterface                   $duplicateObjectFactory
     */
    public function __construct(
        DataTransformerFactoryInterface $dataTransformerFactory,
        EntityManagerInterface $entityManager,
        DuplicateRepositoryInterface $duplicateRepository,
        DuplicateObjectRepositoryInterface $duplicateObjectRepository,
        FactoryInterface $duplicateFactory,
        FactoryInterface $duplicateObjectFactory
    ) {
        $this->dataTransformerFactory = $dataTransformerFactory;
        $this->entityManager = $entityManager;
        $this->duplicateRepository = $duplicateRepository;
        $this->duplicateObjectRepository = $duplicateObjectRepository;
        $this->duplicateFactory = $duplicateFactory;
        $this->duplicateObjectFactory = $duplicateObjectFactory;
    }

    /**
     * {@inheritdoc}
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
     * @param MetadataInterface $metadata
     * @param Concrete          $concrete
     * @param array             $duplicateDataRows
     *
     * @throws NonUniqueResultException
     */
    protected function updateDuplicateIndex(MetadataInterface $metadata, Concrete $concrete, array $duplicateDataRows): void
    {
        $this->duplicateRepository->deleteForObject($concrete);
        $this->duplicateObjectRepository->deleteForObject($concrete);

        foreach ($duplicateDataRows as $group => $duplicateDataRow) {
            $metadataGroup = $metadata->getGroup($group);
            $fieldCombination = null !== $metadataGroup ? $metadataGroup->getFieldKeys() : [];

            $dataMd5 = md5(json_encode($duplicateDataRow));
            $fieldCombinationCrc = crc32(implode(',', $fieldCombination));

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

    /**
     * @param string                 $algorithm
     * @param array                  $duplicateData
     * @param GroupMetadataInterface $groupMetadata
     *
     * @return string
     */
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

        if (!sizeof($data)) {
            return null;
        }

        foreach ($data as $key => $value) {
            if ($algorithm === 'soundex') {
                $data[$key] = soundex($value);
            } elseif ($algorithm === 'metaphone') {
                $data[$key] = metaphone($value);
            }
        }

        return implode('', $data);
    }

    /**
     * @param $value
     * @param FieldMetadataInterface $field
     *
     * @return mixed
     */
    protected function transformData($value, FieldMetadataInterface $field)
    {
        if ($field->getTransformerIdentifier()) {
            return $this->dataTransformerFactory->getInstance($field->getTransformerIdentifier())->transform($value);
        }

        return $value;
    }

    /**
     * @param Concrete $concrete
     *
     * @return bool
     */
    protected function isRelevantForIndex(Concrete $concrete): bool
    {
        return $concrete->getPublished();
    }
}
