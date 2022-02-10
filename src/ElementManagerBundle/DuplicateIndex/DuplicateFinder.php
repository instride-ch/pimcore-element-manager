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

namespace Wvision\Bundle\ElementManagerBundle\DuplicateIndex;

use CoreShop\Component\Resource\Factory\FactoryInterface;
use Doctrine\ORM\EntityManagerInterface;
use Wvision\Bundle\ElementManagerBundle\DuplicateIndex\Similarity\SimilarityCheckerFactoryInterface;
use Wvision\Bundle\ElementManagerBundle\Metadata\DuplicatesIndex\GroupMetadataInterface;
use Wvision\Bundle\ElementManagerBundle\Metadata\DuplicatesIndex\MetadataInterface;
use Wvision\Bundle\ElementManagerBundle\Model\DuplicateInterface;
use Wvision\Bundle\ElementManagerBundle\Model\DuplicateObjectInterface;
use Wvision\Bundle\ElementManagerBundle\Model\PotentialDuplicateInterface;
use Wvision\Bundle\ElementManagerBundle\Repository\DuplicateObjectRepositoryInterface;
use Wvision\Bundle\ElementManagerBundle\Repository\DuplicateRepositoryInterface;
use Wvision\Bundle\ElementManagerBundle\Repository\PotentialDuplicateRepositoryInterface;

class DuplicateFinder implements DuplicateFinderInterface
{
    /**
     * @var SimilarityCheckerFactoryInterface
     */
    private $similarityCheckerFactory;

    /**
     * @var DuplicateRepositoryInterface
     */
    private $duplicateRepository;

    /**
     * @var DuplicateObjectRepositoryInterface
     */
    private $duplicateObjectRepository;

    /**
     * @var PotentialDuplicateRepositoryInterface
     */
    private $potentialDuplicateRepository;

    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

    /**
     * @var FactoryInterface
     */
    private $potentialDuplicateFactory;

    /**
     * @param SimilarityCheckerFactoryInterface     $similarityCheckerFactory
     * @param DuplicateRepositoryInterface          $duplicateRepository
     * @param DuplicateObjectRepositoryInterface    $duplicateObjectRepository
     * @param PotentialDuplicateRepositoryInterface $potentialDuplicateRepository
     * @param EntityManagerInterface                $entityManager
     * @param FactoryInterface                      $potentialDuplicateFactory
     */
    public function __construct(
        SimilarityCheckerFactoryInterface $similarityCheckerFactory,
        DuplicateRepositoryInterface $duplicateRepository,
        DuplicateObjectRepositoryInterface $duplicateObjectRepository,
        PotentialDuplicateRepositoryInterface $potentialDuplicateRepository,
        EntityManagerInterface $entityManager,
        FactoryInterface $potentialDuplicateFactory
    ) {
        $this->similarityCheckerFactory = $similarityCheckerFactory;
        $this->duplicateRepository = $duplicateRepository;
        $this->duplicateObjectRepository = $duplicateObjectRepository;
        $this->potentialDuplicateRepository = $potentialDuplicateRepository;
        $this->entityManager = $entityManager;
        $this->potentialDuplicateFactory = $potentialDuplicateFactory;
    }

    /**
     * {@inheritdoc}
     */
    public function findPotentialDuplicate(MetadataInterface $metadata): void
    {
        $this->potentialDuplicateRepository->deleteAll();

        $result = [];

        $result = array_merge($result, $this->findExactDuplicates($metadata));
        $result = array_merge($result, $this->findFuzzyDuplicates($metadata));

        $paired = [];

        /**
         * @var DuplicateObjectInterface $duplicateObjectFrom
         * @var DuplicateObjectInterface $duplicateObjectTo
         */
        foreach ($result as [$duplicateObjectFrom, $duplicateObjectTo]) {
            if ($duplicateObjectFrom->getObject() === $duplicateObjectTo->getObject()) {
                continue;
            }

            $potentialDuplicate = $this->potentialDuplicateRepository->findDuplication($duplicateObjectFrom, $duplicateObjectTo);

            if ($potentialDuplicate) {
                continue;
            }

            $pairString1 = $duplicateObjectTo->getId() . $duplicateObjectFrom->getId();
            $pairString2 = $duplicateObjectFrom->getId() . $duplicateObjectTo->getId();

            if (in_array($pairString1, $paired, true) || in_array($pairString2, $paired, true)) {
                continue;
            }

            $paired[] = $pairString1;
            $paired[] = $pairString2;

            /**
             * @var PotentialDuplicateInterface $potentialDuplicate
             */
            $potentialDuplicate = $this->potentialDuplicateFactory->createNew();
            $potentialDuplicate->setDuplicateFrom($duplicateObjectFrom);
            $potentialDuplicate->setDuplicateTo($duplicateObjectTo);

            $this->entityManager->persist($potentialDuplicate);
        }

        $this->entityManager->flush();
    }

    /**
     * @param MetadataInterface $metadata
     *
     * @return array
     */
    protected function findFuzzyDuplicates(MetadataInterface $metadata): array
    {
        $soundex = $this->findFuzzyDuplicatesByAlgorithm($metadata, 'soundex');
        $metaphone = $this->findFuzzyDuplicatesByAlgorithm($metadata, 'metaphone');

        return array_merge($soundex, $metaphone);
    }

    /**
     * @param MetadataInterface $metadata
     * @param string            $algorithm
     *
     * @return array
     */
    protected function findFuzzyDuplicatesByAlgorithm(
        MetadataInterface $metadata,
        string $algorithm
    ): array {
        $duplicates = $this->duplicateRepository->findExactByAlgorithm($metadata->getClassName(), $algorithm);
        $result = [];

        foreach ($duplicates as $duplicate) {
            $duplicateObjects = $this->duplicateObjectRepository->findByDuplicateAndAlgorithmValue(
                $duplicate->getObject()->getId(),
                $algorithm,
                $algorithm === 'soundex' ? $duplicate->getSoundex() : $duplicate->getMetaphone()
            );

            $result[] = $this->checkForDuplicate($metadata, $duplicateObjects);
        }

        if (count($result) > 0) {
            $result = array_merge(...$result);
        }

        return $result;
    }

    /**
     * @param MetadataInterface          $metadata
     * @param DuplicateObjectInterface[] $duplicateObjects
     *
     * @return array
     */
    protected function checkForDuplicate(
        MetadataInterface $metadata,
        array $duplicateObjects
    ): array {
        $grouped = [];

        foreach ($duplicateObjects as $duplicateObject) {
            $group = $duplicateObject->getDuplicate()->getGroup();

            if (!isset($grouped[$group])) {
                $grouped[$group] = [];
            }

            $grouped[$group][$duplicateObject->getId()] = $duplicateObject;
        }

        $result = [];

        foreach ($grouped as $group => $duplicates) {
            $result[] = $this->checkForDuplicatesInGroup($metadata->getGroup($group), $duplicates);
        }

        return array_merge(...$result);
    }

    /**
     * @param GroupMetadataInterface $group
     * @param array                  $duplicateObjects
     *
     * @return array
     */
    private function checkForDuplicatesInGroup(
        GroupMetadataInterface $group,
        array $duplicateObjects
    ): array {
        $result = [];

        foreach ($duplicateObjects as $duplicateObject1) {
            foreach ($duplicateObjects as $duplicateObject2) {
                if ($this->duplicatesAreSimilar($group, $duplicateObject1, $duplicateObject2)) {
                    $result[] = [$duplicateObject1, $duplicateObject2];
                }
            }
        }

        return $result;
    }

    /**
     * @param GroupMetadataInterface   $group
     * @param DuplicateObjectInterface $duplicateObject1
     * @param DuplicateObjectInterface $duplicateObject2
     *
     * @return bool
     */
    protected function duplicatesAreSimilar(
        GroupMetadataInterface $group,
        DuplicateObjectInterface $duplicateObject1,
        DuplicateObjectInterface $duplicateObject2
    ): bool {
        $applies = false;
        foreach ($group->getFields() as $field) {
            if ($field->getSimilarityIdentifier()) {
                $applies = true;

                break;
            }
        }

        if (!$applies) {
            return false;
        }

        foreach ($group->getFields() as $field) {
            if ($field->getSimilarityIdentifier()) {
                $checker = $this->similarityCheckerFactory->getInstance($field->getSimilarityIdentifier());

                $dataRow1 = $duplicateObject1->getDuplicate()->getData();
                $dataRow2 = $duplicateObject2->getDuplicate()->getData();

                if (!$checker->isSimilar($dataRow1[$field->getName()], $dataRow2[$field->getName()], $field)) {
                    return false;
                }
            }
        }

        return true;
    }

    /**
     * @param MetadataInterface $metadata
     *
     * @return array
     */
    protected function findExactDuplicates(MetadataInterface $metadata): array
    {
        $duplicateObjects = $this->duplicateObjectRepository->findExactMatches($metadata->getClassName());

        /**
         * @var DuplicateInterface[] $duplicates
         */
        $duplicates = array_map(static function (DuplicateObjectInterface $duplicateObject) {
            return $duplicateObject->getDuplicate();
        }, $duplicateObjects);

        $result = [];

        foreach ($duplicates as $duplicate) {
            $result[] = $this->duplicateObjectRepository->findByDuplicate($duplicate);
        }

        if (count($result) > 0) {
            $result = array_merge(...$result);
        }

        $finalResult = [];

        foreach ($result as $res) {
            foreach ($result as $res2) {
                $finalResult[] = [$res, $res2];
            }
        }

        return $finalResult;
    }
}
