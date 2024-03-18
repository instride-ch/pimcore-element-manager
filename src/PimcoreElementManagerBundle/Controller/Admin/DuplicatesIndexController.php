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

namespace Instride\Bundle\PimcoreElementManagerBundle\Controller\Admin;

use CoreShop\Bundle\ResourceBundle\Controller\ResourceController;
use Instride\Bundle\PimcoreElementManagerBundle\Metadata\DuplicatesIndex\MetadataInterface;
use Instride\Bundle\PimcoreElementManagerBundle\Metadata\DuplicatesIndex\MetadataRegistryInterface;
use Instride\Bundle\PimcoreElementManagerBundle\Model\PotentialDuplicateInterface;
use Instride\Bundle\PimcoreElementManagerBundle\Repository\PotentialDuplicateRepositoryInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Webmozart\Assert\Assert;

class DuplicatesIndexController extends ResourceController
{
    public function listAction(Request $request): JsonResponse
    {
        return $this->viewHandler->handle($this->getMetadataRegistry()->all(), [
            'group' => 'List',
        ]);
    }

    public function getAction(Request $request): JsonResponse
    {
        $this->isGrantedOr403();

        $resource = $this->findByClassNameOr404($request->get('className'));

        return $this->viewHandler->handle(
            [
                'data' => $resource,
                'options' => [
                    'merge_supported' => $this->getParameter('pimcore_element_manager.merge_supported'),
                ],
                'success' => true,
            ],
            [
                'group' => 'Detailed',
            ]
        );
    }

    public function getPotentialDuplicatesAction(Request $request): JsonResponse
    {
        $declined = $request->get('declined', false);
        $metadata = $this->findByClassNameOr404($request->get('className'));
        $offset = $request->get('offset', 0);
        $limit = $request->get('limit', 50);

        if ($declined === 'true') {
            $declined = true;
        } else {
            $declined = false;
        }

        /** @var PotentialDuplicateRepositoryInterface $repository */
        $repository = $this->repository;

        Assert::isInstanceOf($repository, PotentialDuplicateRepositoryInterface::class);

        $result = $repository->findForClassName($metadata->getClassName(), $declined, $offset, $limit);
        $count = $repository->findCountForClassName($metadata->getClassName(), $declined);

        $listResult = [];

        foreach ($result as $res) {
            $fromObject = $res->getDuplicateFrom()->getObject();
            $toObject = $res->getDuplicateTo()->getObject();

            $fromResult = [
                'objectId' => $fromObject->getId(),
                'extId' => \sprintf('%s-%s-from', $fromObject->getId(), $res->getId()),
            ];
            $toResult = [
                'objectId' => $toObject->getId(),
                'extId' => \sprintf('%s-%s-to', $toObject->getId(), $res->getId()),
            ];

            foreach ($metadata->getListFields() as $listField) {
                if (!\is_array($listResult)) {
                    $listField = [$listField];
                }

                $listFieldId = \implode(',', $listField);

                $fromResult[$listFieldId] = [];
                $toResult[$listFieldId] = [];

                foreach ($listField as $field) {
                    $fromResult[$listFieldId][] = $fromObject->get($field);
                    $toResult[$listFieldId][] = $toObject->get($field);
                }

                $fromResult[$listFieldId] = \implode(' ', $fromResult[$listFieldId]);
                $toResult[$listFieldId] = \implode(' ', $toResult[$listFieldId]);
            }

            $fromResult['duplicationId'] = $res->getId();
            $fromResult['declined'] = $res->getDeclined();
            $fromResult['objectIdOther'] = $toObject->getId();
            $fromResult['_isFirstColumn'] = true;

            $toResult['duplicationId'] = $res->getId();
            $toResult['declined'] = $res->getDeclined();
            $toResult['objectIdOther'] = $fromObject->getId();
            $toResult['_isFirstColumn'] = false;

            $listResult[] = $fromResult;
            $listResult[] = $toResult;
        }

        return $this->viewHandler->handle(['total' => $count * 2, 'data' => $listResult, 'success' => true], ['group' => 'Detailed']);
    }

    public function declineDuplicationAction(Request $request): JsonResponse
    {
        /** @var PotentialDuplicateInterface $potentialDuplicate */
        $potentialDuplicate = $this->repository->find($request->get('id'));

        if (!$potentialDuplicate) {
            throw $this->createNotFoundException();
        }

        $potentialDuplicate->setDeclined(true);

        $this->manager->persist($potentialDuplicate);
        $this->manager->flush();

        return $this->viewHandler->handle(['success' => true]);
    }

    public function unDeclineDuplicationAction(Request $request): JsonResponse
    {
        /** @var PotentialDuplicateInterface $potentialDuplicate */
        $potentialDuplicate = $this->repository->find($request->get('id'));

        if (!$potentialDuplicate) {
            throw $this->createNotFoundException();
        }

        $potentialDuplicate->setDeclined(false);

        $this->manager->persist($potentialDuplicate);
        $this->manager->flush();

        return $this->viewHandler->handle(['success' => true]);
    }

    protected function findByClassNameOr404(string $className): MetadataInterface
    {
        if (!$this->getMetadataRegistry()->has($className)) {
            throw $this->createNotFoundException(\sprintf('The "%s" has not been found', $className));
        }

        return $this->getMetadataRegistry()->get($className);
    }

    private function getMetadataRegistry(): MetadataRegistryInterface
    {
        return $this->get(MetadataRegistryInterface::class);
    }
}
