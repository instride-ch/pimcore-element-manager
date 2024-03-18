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

namespace Instride\Bundle\PimcoreElementManagerBundle;

use Composer\InstalledVersions;
use CoreShop\Bundle\ResourceBundle\AbstractResourceBundle;
use CoreShop\Bundle\ResourceBundle\CoreShopResourceBundle;
use Instride\Bundle\PimcoreElementManagerBundle\DependencyInjection\CompilerPass\AddDataTransformersPass;
use Instride\Bundle\PimcoreElementManagerBundle\DependencyInjection\CompilerPass\AddSaveHandlerPass;
use Instride\Bundle\PimcoreElementManagerBundle\DependencyInjection\CompilerPass\AddSimilarityCheckerPass;
use Pimcore\Extension\Bundle\Traits\PackageVersionTrait;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Validator\DependencyInjection\AddConstraintValidatorsPass;

class PimcoreElementManagerBundle extends AbstractResourceBundle
{
    use PackageVersionTrait;

    /**
     * @inheritDoc
     */
    public function getSupportedDrivers(): array
    {
        return [
            CoreShopResourceBundle::DRIVER_DOCTRINE_ORM,
        ];
    }

    protected function getModelNamespace(): ?string
    {
        return 'Instride\Bundle\PimcoreElementManagerBundle\Model';
    }

    /**
     * @inheritDoc
     */
    public function build(ContainerBuilder $builder): void
    {
        parent::build($builder);

        $builder->addCompilerPass(new AddConstraintValidatorsPass('duplication_checker.validator_factory', 'duplication_checker.constraint_validator'));
        $builder->addCompilerPass(new AddDataTransformersPass());
        $builder->addCompilerPass(new AddSimilarityCheckerPass());
        $builder->addCompilerPass(new AddSaveHandlerPass());
    }

    /**
     * @inheritDoc
     */
    public function getNiceName(): string
    {
        return 'Pimcore Element Manager';
    }

    /**
     * @inheritDoc
     */
    public function getDescription(): string
    {
        return 'Manages Pimcore Element\'s with ease.';
    }

    /**
     * @inheritDoc
     */
    protected function getComposerPackageName(): string
    {
        return 'instride/pimcore-element-manager';
    }

    public function getVersion(): string
    {
        $bundleName = 'instride/pimcore-element-manager';

        if (\class_exists(InstalledVersions::class) && InstalledVersions::isInstalled($bundleName)) {
            return InstalledVersions::getVersion($bundleName);
        }

        return '';
    }
}
