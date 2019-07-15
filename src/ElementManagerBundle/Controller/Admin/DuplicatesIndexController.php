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

namespace Wvision\Bundle\ElementManagerBundle\Controller\Admin;

use CoreShop\Bundle\ResourceBundle\Controller\ResourceController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Webmozart\Assert\Assert;
use Wvision\Bundle\ElementManagerBundle\Metadata\DuplicatesIndex\MetadataInterface;
use Wvision\Bundle\ElementManagerBundle\Metadata\DuplicatesIndex\MetadataRegistryInterface;
use Wvision\Bundle\ElementManagerBundle\Repository\PotentialDuplicateRepository;
use Wvision\Bundle\ElementManagerBundle\Repository\PotentialDuplicateRepositoryInterface;

final class DuplicatesIndexController extends ResourceController
{
    /**
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function listAction(Request $request)
    {
        return $this->viewHandler->handle($this->getMetadataRegistry()->all(), ['group' => 'List']);
    }

    /**
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function getAction(Request $request)
    {
        $this->isGrantedOr403();

        $resource = $this->findOr404($request->get('className'));

        return $this->viewHandler->handle(['data' => $resource, 'success' => true], ['group' => 'Detailed']);
    }

    /**
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function getPotentialDuplicatesAction(Request $request)
    {
        $declined = $request->get('declined', false);
        $metadata = $this->findOr404($request->get('className'));
        $offset = $request->get('offset', 0);
        $limit = $request->get('limit', 50);

        if ($declined === 'true') {
            $declined = true;
        }
        else {
            $declined = false;
        }

        /**
         * @var $repository PotentialDuplicateRepositoryInterface
         */
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
                'extId' => $fromObject->getId() . '-' . $res->getId() . '-from',
            ];
            $toResult = [
                'objectId' => $toObject->getId(),
                'extId' => $toObject->getId() . '-' . $res->getId() . '-to',
            ];

            foreach ($metadata->getListFields() as $listField) {
                if (!is_array($listResult)) {
                    $listField = [$listField];
                }

                $listFieldId = implode(',', $listField);

                $fromResult[$listFieldId] = [];
                $toResult[$listFieldId] = [];

                foreach ($listField as $field) {
                    $fromResult[$listFieldId][] = $fromObject->get($field);
                    $toResult[$listFieldId][] = $toObject->get($field);
                }

                $fromResult[$listFieldId] = implode(' ', $fromResult[$listFieldId]);
                $toResult[$listFieldId] = implode(' ', $toResult[$listFieldId]);
            }

            $fromResult['duplicationId'] = $res->getId();
            $fromResult['declined'] = $res->getDeclined();

            $toResult['duplicationId'] = $res->getId();
            $toResult['declined'] = $res->getDeclined();

            $listResult[] = $fromResult;
            $listResult[] = $toResult;
        }

        return $this->viewHandler->handle(['total' => $count * 2, 'data' => $listResult, 'success' => true], ['group' => 'Detailed']);
    }

    /**
     * @param string $className
     *
     * @return MetadataInterface
     *
     * @throws NotFoundHttpException
     */
    protected function findOr404($className)
    {
        if (!$this->getMetadataRegistry()->has($className)) {
            throw new NotFoundHttpException(sprintf('The "%s" has not been found', $className));
        }

        return $this->getMetadataRegistry()->get($className);
    }

    /**
     * @return MetadataRegistryInterface
     */
    private function getMetadataRegistry()
    {
        return $this->get(MetadataRegistryInterface::class);
    }
}
