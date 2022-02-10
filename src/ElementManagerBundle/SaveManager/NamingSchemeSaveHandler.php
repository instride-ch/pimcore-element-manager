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

namespace Wvision\Bundle\ElementManagerBundle\SaveManager;

use Exception;
use Wvision\Bundle\ElementManagerBundle\SaveManager\NamingScheme\NamingSchemeInterface;
use Pimcore\Model\DataObject\Concrete;

final class NamingSchemeSaveHandler extends AbstractObjectSaveHandler
{
    /**
     * @var NamingSchemeInterface
     */
    private $namingScheme;

    /**
     * @param NamingSchemeInterface $namingScheme
     */
    public function __construct(NamingSchemeInterface $namingScheme)
    {
        $this->namingScheme = $namingScheme;
    }

    /**
     * {@inheritdoc}
     *
     * @throws Exception
     */
    public function preSave(Concrete $object, array $options): void
    {
        $this->namingScheme->apply($object, $options['naming_scheme'] ?? []);
    }
}
