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
use Instride\Bundle\PimcoreElementManagerBundle\DuplicateIndex\Similarity\SimilarityCheckerFactoryInterface;
use Instride\Bundle\PimcoreElementManagerBundle\Metadata\DuplicatesIndex\GroupMetadataInterface;
use Instride\Bundle\PimcoreElementManagerBundle\Metadata\DuplicatesIndex\MetadataInterface;
use Instride\Bundle\PimcoreElementManagerBundle\Model\DuplicateInterface;
use Instride\Bundle\PimcoreElementManagerBundle\Model\DuplicateObjectInterface;
use Instride\Bundle\PimcoreElementManagerBundle\Model\PotentialDuplicateInterface;
use Instride\Bundle\PimcoreElementManagerBundle\Repository\DuplicateObjectRepositoryInterface;
use Instride\Bundle\PimcoreElementManagerBundle\Repository\DuplicateRepositoryInterface;
use Instride\Bundle\PimcoreElementManagerBundle\Repository\PotentialDuplicateRepositoryInterface;

class DuplicateFinder implements DuplicateFinderInterface
{
    public function __construct(
        private readonly SimilarityCheckerFactoryInterface $similarityCheckerFactory,
        private readonly DuplicateRepositoryInterface $duplicateRepository,
        private readonly DuplicateObjectRepositoryInterface $duplicateObjectRepository,
        private readonly PotentialDuplicateRepositoryInterface $potentialDuplicateRepository,
        private readonly EntityManagerInterface $entityManager,
        private readonly FactoryInterface $potentialDuplicateFactory
    ) {}

    public function findPotentialDuplicate(MetadataInterface $metadata): void
    {
        $this->potentialDuplicateRepository->deleteAll();

        $result = [];

        $result = \array_merge($result, $this->findExactDuplicates($metadata));
        $result = \array_merge($result, $this->findFuzzyDuplicates($metadata));

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

            if (\in_array($pairString1, $paired, true) || \in_array($pairString2, $paired, true)) {
                continue;
            }

            $paired[] = $pairString1;
            $paired[] = $pairString2;

            /** @var PotentialDuplicateInterface $potentialDuplicate */
            $potentialDuplicate = $this->potentialDuplicateFactory->createNew();
            $potentialDuplicate->setDuplicateFrom($duplicateObjectFrom);
            $potentialDuplicate->setDuplicateTo($duplicateObjectTo);

            $this->entityManager->persist($potentialDuplicate);
        }

        $this->entityManager->flush();
    }

    protected function findFuzzyDuplicates(MetadataInterface $metadata): array
    {
        $soundex = $this->findFuzzyDuplicatesByAlgorithm($metadata, 'soundex');
        $metaphone = $this->findFuzzyDuplicatesByAlgorithm($metadata, 'metaphone');

        return \array_merge($soundex, $metaphone);
    }

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

        if (\count($result) > 0) {
            $result = \array_merge(...$result);
        }

        return $result;
    }

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

        return \array_merge(...$result);
    }

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

    protected function findExactDuplicates(MetadataInterface $metadata): array
    {
        $duplicateObjects = $this->duplicateObjectRepository->findExactMatches($metadata->getClassName());

        /** @var DuplicateInterface[] $duplicates */
        $duplicates = \array_map(static function (DuplicateObjectInterface $duplicateObject) {
            return $duplicateObject->getDuplicate();
        }, $duplicateObjects);

        $result = [];

        foreach ($duplicates as $duplicate) {
            $result[] = $this->duplicateObjectRepository->findByDuplicate($duplicate);
        }

        if (\count($result) > 0) {
            $result = \array_merge(...$result);
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
