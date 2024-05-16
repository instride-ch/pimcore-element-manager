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

namespace Instride\Bundle\PimcoreElementManagerBundle\DependencyInjection;

use CoreShop\Bundle\ResourceBundle\DependencyInjection\Extension\AbstractModelExtension;
use Instride\Bundle\PimcoreElementManagerBundle\Metadata\DuplicatesIndex\FieldMetadata;
use Instride\Bundle\PimcoreElementManagerBundle\Metadata\DuplicatesIndex\GroupMetadata;
use Instride\Bundle\PimcoreElementManagerBundle\Metadata\DuplicatesIndex\Metadata;
use Instride\Bundle\PimcoreElementManagerBundle\Metadata\DuplicatesIndex\MetadataRegistry;
use Instride\Bundle\PimcoreElementManagerBundle\SaveManager\DuplicationSaveHandler;
use Instride\Bundle\PimcoreElementManagerBundle\SaveManager\NamingSchemeSaveHandler;
use Instride\Bundle\PimcoreElementManagerBundle\SaveManager\ObjectSaveManagers;
use Instride\Bundle\PimcoreElementManagerBundle\SaveManager\UniqueKeySaveHandler;
use Instride\Bundle\PimcoreElementManagerBundle\SaveManager\ValidationSaveHandler;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\Config\Resource\DirectoryResource;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Loader;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\Finder\Finder;
use Symfony\Component\HttpFoundation\File\File;

class PimcoreElementManagerExtension extends AbstractModelExtension
{
    /**
     * @inheritDoc
     *
     * @throws \Exception
     */
    public function load(array $configs, ContainerBuilder $container): void
    {
        $loader = new Loader\YamlFileLoader($container, new FileLocator(__DIR__ . '/../Resources/config'));
        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        $loader->load('services.yaml');
        $loader->load('services/data_transformer.yaml');
        $loader->load('services/duplication.yaml');
        $loader->load('services/similarity_checker.yaml');
        $loader->load('services/commands.yaml');

        $this->registerResources(
            'pimcore_element_manager',
            $config['driver'],
            $config['resources'],
            $container
        );
        $this->registerPimcoreResources(
            'pimcore_element_manager',
            $config['pimcore_admin'],
            $container
        );

        $bundles = $container->getParameter('kernel.bundles');
        $isMergeSupported = \array_key_exists('ObjectMergerBundle', $bundles);

        $container->setParameter('pimcore_element_manager.merge_supported', $isMergeSupported);

        $this->registerDuplicationCheckerConfiguration($config['duplication'] ?? [], $container, $loader);

        $objectSaveManagers = new Definition(ObjectSaveManagers::class);
        $container->setDefinition(ObjectSaveManagers::class, $objectSaveManagers);

        foreach ($config['classes'] ?? [] as $className => $classConfig) {
            $this->registerSaveManagerConfiguration(
                $container,
                $className,
                $classConfig ?? [],
                $loader,
                $objectSaveManagers
            );
            $this->registerDuplicateIndexConfiguration(
                $container,
                $className,
                $classConfig['duplicates_index'] ?? []
            );
        }
    }

    /**
     * @throws \Exception
     */
    private function registerSaveManagerConfiguration(
        ContainerBuilder $container,
        string $className,
        array $config,
        Loader\YamlFileLoader $loader,
        Definition $objectSaveManagers
    ): void {
        $loader->load('services/save_manager.yaml');

        $definition = new Definition($config['save_manager_class']);
        $options = [
            'naming_scheme' => $config['naming_scheme']['options'] ?? null,
            'duplicates' => $config['duplicates']['options'] ?? null,
            'validations' => $config['validations']['options'] ?? null,
        ];

        if (isset($config['naming_scheme']['enabled']) && $config['naming_scheme']['enabled'] === true) {
            $namingDefinition = new Definition(NamingSchemeSaveHandler::class, [
                new Reference($config['naming_scheme']['service']),
            ]);

            $namingDefinition->setPublic(false);
            $container->setDefinition(
                \sprintf('save_manager.naming_scheme.%s', \strtolower($className)),
                $namingDefinition
            );

            $definition->addMethodCall('addSaveHandler', [
                new Reference(\sprintf('save_manager.naming_scheme.%s', \strtolower($className))),
            ]);
        }

        if (isset($config['unique_key']['enabled']) && $config['unique_key']['enabled'] === true) {
            $definition->addMethodCall('addSaveHandler', [new Reference(UniqueKeySaveHandler::class)]);
        }

        if (isset($config['validations']['enabled_on_save']) && $config['validations']['enabled_on_save'] === true) {
            $definition->addMethodCall('addSaveHandler', [new Reference(ValidationSaveHandler::class)]);
        }

        if (isset($config['duplicates']['enabled_on_save']) && $config['duplicates']['enabled_on_save'] === true) {
            $definition->addMethodCall('addSaveHandler', [new Reference(DuplicationSaveHandler::class)]);
        }

        if (isset($config['save_handlers']) && \is_array($config['save_handlers'])) {
            foreach ($config['save_handlers'] as $saveHandler) {
                $definition->addMethodCall('addSaveHandler', [new Reference($saveHandler)]);
            }
        }

        $definition->addMethodCall('setOptions', [$options]);

        $container->setDefinition(\sprintf('save_manager.%s', \strtolower($className)), $definition);

        $objectSaveManagers->addMethodCall(
            'addSaveManager',
            [
                $className,
                new Reference(\sprintf('save_manager.%s', \strtolower($className))),
            ]
        );
        $container->setDefinition(ObjectSaveManagers::class, $objectSaveManagers);
    }

    /**
     * @throws \Exception
     */
    private function registerDuplicationCheckerConfiguration(
        array $config,
        ContainerBuilder $container,
        Loader\YamlFileLoader $loader
    ): void {
        $loader->load('services/duplication.yaml');

        $duplicationBuilder = $container->getDefinition('duplication_checker.builder');

        $files = ['yaml' => []];
        $this->registerDuplicationCheckerMapping($container, $config, $files);

        if (!empty($files['yaml'])) {
            $duplicationBuilder->addMethodCall('addYamlMappings', [$files['yaml']]);
        }

        if (!empty($files['xml'])) {
            $duplicationBuilder->addMethodCall('addXmlMappings', [$files['yaml']]);
        }

        if (!$container->getParameter('kernel.debug')) {
            $duplicationBuilder->addMethodCall('setMappingCache', [
                new Reference('duplication_checker.mapping.cache.adapter'),
            ]);
        }
    }

    /**
     * @param ContainerBuilder $container
     * @param array            $config
     * @param array            $files
     */
    private function registerDuplicationCheckerMapping(ContainerBuilder $container, array $config, array &$files): void
    {
        $fileRecorder = static function ($extension, $path) use (&$files) {
            $files[$extension][] = $path;
        };

        foreach ($container->getParameter('kernel.bundles_metadata') as $bundle) {
            $dirname = $bundle['path'];

            if ($container->fileExists($file = $dirname . '/Resources/config/duplication.yaml', false)) {
                $fileRecorder('yaml', $file);
            }

            if ($container->fileExists($file = $dirname . '/Resources/config/duplication.xml', false)) {
                $fileRecorder('xml', $file);
            }

            if ($container->fileExists($dir = $dirname . '/Resources/config/duplication', '/^$/')) {
                $this->registerMappingFilesFromDir($dir, $fileRecorder);
            }
        }

        $projectDir = $container->getParameter('kernel.project_dir');

        if ($container->fileExists($dir = $projectDir . '/config/duplication', '/^$/')) {
            $this->registerMappingFilesFromDir($dir, $fileRecorder);
        }

        if (isset($config['mapping']['paths']) && \is_array($config['mapping']['paths'])) {
            $this->registerMappingFilesFromConfig($container, $config, $fileRecorder);
        }
    }

    private function registerMappingFilesFromDir($dir, callable $fileRecorder): void
    {
        $files = Finder::create()
            ->followLinks()
            ->files()
            ->in($dir)
            ->name('/\.(xml|ya?ml)$/')
            ->sortByName();

        /** @var File $file */
        foreach ($files as $file) {
            $fileRecorder($file->getExtension(), $file->getRealPath());
        }
    }

    private function registerMappingFilesFromConfig(
        ContainerBuilder $container,
        array $config,
        callable $fileRecorder
    ): void {
        foreach ($config['mapping']['paths'] as $path) {
            if (\is_dir($path)) {
                $this->registerMappingFilesFromDir($path, $fileRecorder);
                $container->addResource(new DirectoryResource($path, '/^$/'));
            } elseif ($container->fileExists($path, false)) {
                if (!\preg_match('/\.(xml|ya?ml)$/', $path, $matches)) {
                    throw new \RuntimeException(
                        \sprintf('Unsupported mapping type in "%s", supported types is only Yaml.', $path)
                    );
                }
                $fileRecorder($matches[1], $path);
            } else {
                throw new \RuntimeException(\sprintf('Could not open file or directory "%s".', $path));
            }
        }
    }

    private function registerDuplicateIndexConfiguration(
        ContainerBuilder $container,
        string $className,
        array $config
    ): void {
        if (! isset($config['enabled']) || $config['enabled'] !== true) {
            return;
        }

        $groups = [];

        foreach ($config['groups'] ?? [] as $groupName => $group) {
            $fields = [];

            foreach ($group['fields'] ?? [] as $fieldName => $fieldConfig) {
                $fieldMetaData = new Definition(FieldMetadata::class, [
                    $fieldName, $fieldConfig,
                ]);
                $fieldMetaData->setPublic(false);

                $fieldId = \sprintf(
                    'pimcore_element_manager.metadata.%s.%s.%s',
                    \strtolower($className),
                    \strtolower($groupName),
                    \strtolower($fieldName)
                );

                $container->setDefinition($fieldId, $fieldMetaData);

                $fields[] = new Reference($fieldId);
            }

            $groupMetaData = new Definition(GroupMetadata::class, [$groupName, $fields]);
            $groupMetaData->setPublic(false);

            $groupId = \sprintf(
                'pimcore_element_manager.metadata.%s.%s',
                \strtolower($className),
                \strtolower($groupName)
            );

            $container->setDefinition($groupId, $groupMetaData);
            $groups[] = new Reference($groupId);
        }

        if (\count($groups) === 0) {
            return;
        }

        $listFields = $config['list_fields'] ?? null;

        $metadata = new Definition(Metadata::class, [$className, $groups, $listFields]);

        $container->setDefinition(
            \sprintf('pimcore_element_manager.metadata.%s', \strtolower($className)),
            $metadata
        );

        $container->getDefinition(MetadataRegistry::class)->addMethodCall('register', [
            new Reference(\sprintf('pimcore_element_manager.metadata.%s', \strtolower($className))),
        ]);
    }
}
