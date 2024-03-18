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

namespace Instride\Bundle\PimcoreElementManagerBundle\DuplicateChecker;

use Pimcore\Model\Element\ElementInterface;
use Symfony\Component\Validator\ConstraintViolationInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class DuplicateService implements DuplicateServiceInterface
{
    public function __construct(private readonly ValidatorInterface $duplicateChecker) {}

    /**
     * @inheritDoc
     */
    public function findDuplicates(ElementInterface $element, array $groups = null): array
    {
        $result = $this->duplicateChecker->validate($element, null, $groups);

        if ($result->count()) {
            return \array_map(
                static fn (ConstraintViolationInterface $result) => $result->getInvalidValue(),
                \iterator_to_array($result)
            );
        }

        return [];
    }
}
