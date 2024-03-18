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

namespace Instride\Bundle\PimcoreElementManagerBundle\SaveManager;

use Pimcore\Model\DataObject\Concrete;
use Pimcore\Model\Element\ValidationException;
use Symfony\Component\Validator\Validator\ValidatorInterface;

final class ValidationSaveHandler extends AbstractObjectSaveHandler
{
    public function __construct(private readonly ValidatorInterface $validator) {}

    /**
     * @throws ValidationException
     */
    public function preSave(Concrete $object, array $options): void
    {
        if ($object->getOmitMandatoryCheck()) {
            return;
        }

        $results = $this->validator->validate($options, null, [$options['validations']['group']]);

        if ($results->count() > 0) {
            $messages = [];

            foreach ($results as $result) {
                $messages[] = $result->getMessage();
            }

            throw new ValidationException(\implode(PHP_EOL, $messages));
        }
    }
}
