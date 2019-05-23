<?php
/**
 * Element Manager
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

namespace WVision\Bundle\ElementManagerBundle\DuplicateChecker\Constraints;

use WVision\Bundle\ElementManagerBundle\DuplicateChecker\DuplicateConstraint;

class Fields extends DuplicateConstraint
{
    public $message = 'Element with same fields found';

    public $trim = false;

    public $fields;

    public function getDefaultOption()
    {
        return 'fields';
    }

    public function getRequiredOptions()
    {
        return ['fields'];
    }
}
