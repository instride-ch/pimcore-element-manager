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

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidatorInterface;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

class ContainerConstraintValidatorFactory extends \Symfony\Component\Validator\ContainerConstraintValidatorFactory
{
    /**
     * @inheritDoc
     */
    public function getInstance(Constraint $constraint): ConstraintValidatorInterface
    {
        $validator = parent::getInstance($constraint);

        if (!$validator instanceof DuplicateValidatorInterface) {
            throw new UnexpectedTypeException($validator, DuplicateValidatorInterface::class);
        }

        return $validator;
    }
}
