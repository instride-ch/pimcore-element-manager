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

namespace Wvision\Bundle\ElementManagerBundle\SaveManager\NamingScheme;

use Pimcore\Model\DataObject\Concrete;
use Pimcore\Model\DataObject\Service;
use Symfony\Component\ExpressionLanguage\ExpressionLanguage;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ExpressionNamingScheme implements NamingSchemeInterface
{
    /**
     * @var ExpressionLanguage
     */
    private $expressionLanguage;

    /**
     * @param ExpressionLanguage $expressionLanguage
     */
    public function __construct(ExpressionLanguage $expressionLanguage)
    {
        $this->expressionLanguage = $expressionLanguage;
    }

    /**
     * {@inheritdoc}
     */
    public function apply(Concrete $object, array $options): void
    {
        $optionsResolver = new OptionsResolver();
        $optionsResolver->setDefaults([
            'parent_path' => '/',
            'archive_path' => '/_temp',
            'scheme' => '',
            'auto_prefix_path' => true,
        ]);
        $optionsResolver->setRequired([
            'parent_path', 'archive_path', 'scheme', 'auto_prefix_path'
        ]);

        $options = $optionsResolver->resolve($options);

        $autoPrefixPath = $options['auto_prefix_path'];
        $parentPath = $object->getPublished() ? $options['parent_path'] : $options['archive_path'];

        $namingScheme = $this->expressionLanguage->evaluate(
            $options['scheme'],
            array_merge($options, ['object' => $object, 'path' => $parentPath])
        );

        if (is_array($namingScheme)) {
            $key = $namingScheme[count($namingScheme) - 1];
            unset($namingScheme[count($namingScheme) - 1]);

            if ($autoPrefixPath) {
                $parentPath .= '/' . implode('/', $namingScheme);
            }
            else {
                $parentPath = '/' . implode('/', $namingScheme);
            }
        } else {
            $key = $namingScheme;
        }

        $object->setKey($key);
        $parentPath = $this->correctPath($parentPath);

        $object->setParent(Service::createFolderByPath($parentPath));

        if (!$object->getKey()) {
            $object->setKey(uniqid('element', true));
        }

        $object->setKey(Service::getUniqueKey($object));
    }

    /**
     * @param $path
     *
     * @return string
     */
    private function correctPath($path): string
    {
        return str_replace('//', '/', $path);
    }
}
