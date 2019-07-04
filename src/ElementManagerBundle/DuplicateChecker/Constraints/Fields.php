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

namespace Wvision\Bundle\ElementManagerBundle\DuplicateChecker\Constraints;

use Wvision\Bundle\ElementManagerBundle\DuplicateChecker\DuplicateConstraint;

class Fields extends DuplicateConstraint
{
    /**
     * @var string
     */
    public $message = 'Element with same fields found';

    /**
     * @var bool
     */
    public $trim = false;

    /**
     * @var array|null
     */
    public $fields;

    /**
     * @return string|null
     */
    public function getDefaultOption(): ?string
    {
        return 'fields';
    }

    /**
     * @return array
     */
    public function getRequiredOptions(): array
    {
        return ['fields'];
    }
}
