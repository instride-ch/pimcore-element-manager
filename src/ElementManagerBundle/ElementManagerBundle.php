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

namespace WVision\Bundle\ElementManagerBundle;

use CoreShop\Bundle\ResourceBundle\AbstractResourceBundle;
use CoreShop\Bundle\ResourceBundle\CoreShopResourceBundle;
use WVision\Bundle\ElementManagerBundle\DependencyInjection\CompilerPass\AddDataTransformersPass;
use WVision\Bundle\ElementManagerBundle\DependencyInjection\CompilerPass\AddSaveHandlerPass;
use WVision\Bundle\ElementManagerBundle\DependencyInjection\CompilerPass\AddSimilarityCheckerPass;
use Pimcore\Extension\Bundle\Traits\PackageVersionTrait;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Validator\DependencyInjection\AddConstraintValidatorsPass;

class ElementManagerBundle extends AbstractResourceBundle
{
    use PackageVersionTrait;

    /**
     * {@inheritdoc}
     */
    public function getSupportedDrivers()
    {
        return [
            CoreShopResourceBundle::DRIVER_DOCTRINE_ORM,
        ];
    }


    /**
     * {@inheritdoc}
     */
    protected function getModelNamespace()
    {
        return 'WVision\Bundle\ElementManagerBundle\Model';
    }

    /**
     * {@inheritdoc}
     */
    public function build(ContainerBuilder $builder)
    {
        parent::build($builder);

        $builder->addCompilerPass(new AddConstraintValidatorsPass('duplication_checker.validator_factory', 'duplication_checker.constraint_validator'));
        $builder->addCompilerPass(new AddDataTransformersPass());
        $builder->addCompilerPass(new AddSimilarityCheckerPass());
        $builder->addCompilerPass(new AddSaveHandlerPass());
    }

    /**
     * {@inheritdoc}
     */
    public function getNiceName()
    {
        return 'Element Manager Bundle';
    }

    /**
     * {@inheritdoc}
     */
    public function getDescription()
    {
        return 'Element Manager';
    }

    /**
     * {@inheritdoc}
     */
    protected function getComposerPackageName()
    {
        return 'w-vision/import-definitions';
    }

    /**
     * {@inheritdoc}
     */
    public function getJsPaths()
    {
        return [];
    }

    /**
     * {@inheritdoc}
     */
    public function getCssPaths()
    {
        return [];
    }

    /**
     * {@inheritdoc}
     */
    public function getEditmodeJsPaths()
    {
        return [];
    }

    /**
     * {@inheritdoc}
     */
    public function getEditmodeCssPaths()
    {
        return [];
    }
}
