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

namespace Wvision\Bundle\ElementManagerBundle\DependencyInjection;

use CoreShop\Bundle\ResourceBundle\DependencyInjection\Extension\AbstractModelExtension;
use Exception;
use RuntimeException;
use Symfony\Component\HttpFoundation\File\File;
use Wvision\Bundle\ElementManagerBundle\Metadata\DuplicatesIndex\FieldMetadata;
use Wvision\Bundle\ElementManagerBundle\Metadata\DuplicatesIndex\GroupMetadata;
use Wvision\Bundle\ElementManagerBundle\Metadata\DuplicatesIndex\Metadata;
use Wvision\Bundle\ElementManagerBundle\Metadata\DuplicatesIndex\MetadataRegistry;
use Wvision\Bundle\ElementManagerBundle\Metadata\DuplicatesIndex\MetadataRegistryInterface;
use Wvision\Bundle\ElementManagerBundle\SaveManager\DuplicationSaveHandler;
use Wvision\Bundle\ElementManagerBundle\SaveManager\NamingSchemeSaveHandler;
use Wvision\Bundle\ElementManagerBundle\SaveManager\ObjectSaveManagers;
use Wvision\Bundle\ElementManagerBundle\SaveManager\ValidationSaveHandler;
use Symfony\Component\Config\Resource\DirectoryResource;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Loader;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\Finder\Finder;

class ElementManagerExtension extends AbstractModelExtension
{
    /**
     * {@inheritdoc}
     *
     * @throws Exception
     */
    public function load(array $configs, ContainerBuilder $container): void
    {
        $loader = new Loader\YamlFileLoader($container, new FileLocator(__DIR__ . '/../Resources/config'));
        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        $loader->load('services.yml');
        $loader->load('services/data_transformer.yml');
        $loader->load('services/similarity_checker.yml');
        $loader->load('services/commands.yml');

        $this->registerResources(
            'wvision_element_manager',
            $config['driver'],
            $config['resources'],
            $container
        );
        $this->registerPimcoreResources(
            'wvision_element_manager',
            $config['pimcore_admin'],
            $container
        );

        $bundles = $container->getParameter('kernel.bundles');

        if (array_key_exists('ObjectMergerBundle', $bundles)) {
            $container->setParameter('wvision_element_manager.merge_supported', true);
        }
        else {
            $container->setParameter('wvision_element_manager.merge_supported', false);
        }

        $this->registerDuplicationCheckerConfiguration($config['duplication'] ?? [], $container, $loader);

        $objectSaveManagers = new Definition(ObjectSaveManagers::class);
        $container->setDefinition(ObjectSaveManagers::class, $objectSaveManagers);

        foreach ($config['classes'] as $className => $classConfig) {
            $this->registerSaveManagerConfiguration($container, $className, $classConfig ?? [], $loader);
            $this->registerDuplicateIndexConfiguration(
                $container,
                $className,
                $classConfig['duplicates_index'] ?? []
            );
        }
    }

    /**
     * @param ContainerBuilder      $container
     * @param string                $className
     * @param array                 $config
     * @param Loader\YamlFileLoader $loader
     *
     * @throws Exception
     */
    private function registerSaveManagerConfiguration(
        ContainerBuilder $container,
        string $className,
        array $config,
        Loader\YamlFileLoader $loader
    ): void {
        $loader->load('services/save_manager.yml');

        $definition = new Definition($config['save_manager_class']);

        $options = [
            'naming_scheme' => $config['naming_scheme']['options'],
            'duplicates' => $config['duplicates']['options'],
            'validations' => $config['validations']['options'],
        ];

        if ($config['naming_scheme']['enabled']) {
            $namingDefinition = new Definition(NamingSchemeSaveHandler::class, [
                new Reference($config['naming_scheme']['service']),
            ]);

            $namingDefinition->setPrivate(true);
            $container->setDefinition(
                sprintf('save_manager.naming_scheme.%s', strtolower($className)),
                $namingDefinition
            );

            $definition->addMethodCall('addSaveHandler', [
                new Reference(sprintf('save_manager.naming_scheme.%s', strtolower($className))),
            ]);
        }

        if ($config['validations']['enabled_on_save']) {
            $definition->addMethodCall('addSaveHandler', [new Reference(ValidationSaveHandler::class)]);
        }

        if ($config['duplicates']['enabled_on_save']) {
            $definition->addMethodCall('addSaveHandler', [new Reference(DuplicationSaveHandler::class)]);
        }

        if ($config['save_handlers']) {
            foreach ($config['save_handlers'] as $saveHandler) {
                $definition->addMethodCall('addSaveHandler', [new Reference($saveHandler)]);
            }
        }

        $definition->addMethodCall('setOptions', [$options]);

        $container->setDefinition(sprintf('save_manager.%s', strtolower($className)), $definition);

        $container->getDefinition(ObjectSaveManagers::class)->addMethodCall(
            'addSaveManager',
            [
                $className,
                new Reference(sprintf('save_manager.%s', strtolower($className))),
            ]
        );
    }

    /**
     * @param array                 $config
     * @param ContainerBuilder      $container
     * @param Loader\YamlFileLoader $loader
     *
     * @throws Exception
     */
    private function registerDuplicationCheckerConfiguration(
        array $config,
        ContainerBuilder $container,
        Loader\YamlFileLoader $loader
    ): void {
        $loader->load('services/duplication.yml');

        $duplicationBuilder = $container->getDefinition('duplication_checker.builder');

        $files = ['yml' => []];
        $this->registerDuplicationCheckerMapping($container, $config, $files);

        if (!empty($files['yml'])) {
            $duplicationBuilder->addMethodCall('addYamlMappings', [$files['yml']]);
        }

        if (!empty($files['xml'])) {
            $duplicationBuilder->addMethodCall('addXmlMappings', [$files['yml']]);
        }

        if (!$container->getParameter('kernel.debug')) {
            $duplicationBuilder->addMethodCall('setMetadataCache', [
                new Reference('duplication_checker.mapping.cache.symfony'),
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
            $files['yaml' === $extension ? 'yml' : $extension][] = $path;
        };

        foreach ($container->getParameter('kernel.bundles_metadata') as $bundle) {
            $dirname = $bundle['path'];

            if ($container->fileExists($file = $dirname . '/Resources/config/duplication.yaml', false) ||
                $container->fileExists($file = $dirname . '/Resources/config/duplication.yml', false)
            ) {
                $fileRecorder('yml', $file);
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

        if (is_array($config['mapping']['paths'])) {
            $this->registerMappingFilesFromConfig($container, $config, $fileRecorder);
        }
    }

    /**
     * @param $dir
     * @param callable $fileRecorder
     */
    private function registerMappingFilesFromDir($dir, callable $fileRecorder): void
    {
        $files = Finder::create()->followLinks()->files()->in($dir)->name('/\.(xml|ya?ml)$/')->sortByName();

        /** @var File $file */
        foreach ($files as $file) {
            $fileRecorder($file->getExtension(), $file->getRealPath());
        }
    }

    /**
     * @param ContainerBuilder $container
     * @param array            $config
     * @param callable         $fileRecorder
     */
    private function registerMappingFilesFromConfig(
        ContainerBuilder $container,
        array $config,
        callable $fileRecorder
    ): void {
        foreach ($config['mapping']['paths'] as $path) {
            if (is_dir($path)) {
                $this->registerMappingFilesFromDir($path, $fileRecorder);
                $container->addResource(new DirectoryResource($path, '/^$/'));
            } elseif ($container->fileExists($path, false)) {
                if (!preg_match('/\.(xml|ya?ml)$/', $path, $matches)) {
                    throw new RuntimeException(
                        sprintf('Unsupported mapping type in "%s", supported types is only Yaml.', $path)
                    );
                }
                $fileRecorder($matches[1], $path);
            } else {
                throw new RuntimeException(sprintf('Could not open file or directory "%s".', $path));
            }
        }
    }

    /**
     * @param ContainerBuilder $container
     * @param string           $className
     * @param array            $config
     */
    private function registerDuplicateIndexConfiguration(
        ContainerBuilder $container,
        string $className,
        array $config
    ): void {
        if (!$config['enabled']) {
            return;
        }

        $groups = [];

        foreach ($config['groups'] as $groupName => $group) {
            $fields = [];

            foreach ($group['fields'] as $fieldName => $fieldConfig) {
                $fieldMetaData = new Definition(FieldMetadata::class, [
                    $fieldName, $fieldConfig,
                ]);
                $fieldMetaData->setPrivate(true);

                $fieldId = sprintf(
                    'wvision_element_manager.metadata.%s.%s.%s',
                    strtolower($className),
                    strtolower($groupName),
                    strtolower($fieldName)
                );

                $container->setDefinition($fieldId, $fieldMetaData);

                $fields[] = new Reference($fieldId);
            }

            $groupMetaData = new Definition(GroupMetadata::class, [$groupName, $fields]);
            $groupMetaData->setPrivate(true);

            $groupId = sprintf(
                'wvision_element_manager.metadata.%s.%s',
                strtolower($className),
                strtolower($groupName)
            );

            $container->setDefinition($groupId, $groupMetaData);
            $groups[] = new Reference($groupId);
        }

        if (count($groups) === 0) {
            return;
        }

        $listFields = $config['list_fields'];

        $metadata = new Definition(Metadata::class, [$className, $groups, $listFields]);

        $container->setDefinition(
            sprintf('wvision_element_manager.metadata.%s', strtolower($className)),
            $metadata
        );

        $container->getDefinition(MetadataRegistry::class)->addMethodCall('register', [
            new Reference(sprintf('wvision_element_manager.metadata.%s', strtolower($className))),
        ]);
    }
}
