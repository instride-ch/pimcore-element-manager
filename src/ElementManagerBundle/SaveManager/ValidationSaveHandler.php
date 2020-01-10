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
 * @copyright  Copyright (c) 2016-2020 w-vision AG (https://www.w-vision.ch)
 * @license    https://github.com/w-vision/ImportDefinitions/blob/master/gpl-3.0.txt GNU General Public License version 3 (GPLv3)
 */

namespace Wvision\Bundle\ElementManagerBundle\SaveManager;

use Pimcore\Model\DataObject\Concrete;
use Pimcore\Model\Element\ValidationException;
use Symfony\Component\Validator\Validator\ValidatorInterface;

final class ValidationSaveHandler extends AbstractObjectSaveHandler
{
    /**
     * @var ValidatorInterface
     */
    private $validator;

    /**
     * @param ValidatorInterface $validator
     */
    public function __construct(ValidatorInterface $validator)
    {
        $this->validator = $validator;
    }

    /**
     * {@inheritdoc}
     *
     * @throws ValidationException
     */
    public function preSave(Concrete $object, array $options): void
    {
        if ($object->getOmitMandatoryCheck()) {
            return;
        }

        $results = $this->validator->validate($options, null, [$options['group']]);

        if ($results->count() > 0) {
            $messages = [];

            foreach ($results as $result) {
                $messages[] = $result->getMessage();
            }

            throw new ValidationException(implode(PHP_EOL, $messages));
        }
    }
}
