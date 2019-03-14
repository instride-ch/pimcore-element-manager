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

namespace ElementManagerBundle\DependencyInjection;

use ElementManagerBundle\SaveManager\DuplicationSaveHandler;
use ElementManagerBundle\SaveManager\NamingSchemeSaveHandler;
use ElementManagerBundle\SaveManager\ObjectSaveManagers;
use ElementManagerBundle\SaveManager\ValidationSaveHandler;
use Symfony\Component\Config\Resource\DirectoryResource;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Loader;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\Finder\Finder;

class ElementManagerExtension extends Extension
{
    /**
     * {@inheritdoc}
     * @throws \Exception
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $loader = new Loader\YamlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        //$loader->load('services.yml');

        $this->registerDuplicationCheckerConfiguration($config['duplication'] ?? [], $container, $loader);
        $this->registerSaveManagerConfiguration($config['save_manager'] ?? [], $container, $loader);
    }

    private function registerSaveManagerConfiguration(array $config, ContainerBuilder $container, Loader\YamlFileLoader $loader)
    {
        $loader->load('services/save_manager.yml');

        $objectSaveManagers = new Definition(ObjectSaveManagers::class);

        foreach ($config as $class => $classConfig)
        {
            $definition = new Definition($classConfig['class']);

            $options = [
                'naming_scheme' => $classConfig['naming_scheme']['options'],
                'duplicates' => $classConfig['duplicates']['options'],
                'validations' => $classConfig['validations']['options']
            ];

            if ($classConfig['naming_scheme']['enabled']) {
                $namingDefinition = new Definition(NamingSchemeSaveHandler::class, [
                    new Reference($classConfig['naming_scheme']['service'])
                ]);

                $namingDefinition->setPrivate(true);
                $container->setDefinition(sprintf('save_manager.naming_scheme.%s', strtolower($class)), $namingDefinition);

                $definition->addMethodCall('addSaveHandler', [new Reference(sprintf('save_manager.naming_scheme.%s', strtolower($class)))]);
            }

            if ($classConfig['validations']['enabled_on_save']) {
                $definition->addMethodCall('addSaveHandler', [new Reference(ValidationSaveHandler::class)]);
            }

            if ($classConfig['duplicates']['enabled_on_save']) {
                $definition->addMethodCall('addSaveHandler', [new Reference(DuplicationSaveHandler::class)]);
            }

            foreach ($classConfig['save_handlers'] as $saveHandler) {
                $definition->addMethodCall('addSaveHandler', [new Reference($saveHandler)]);
            }


            $definition->addMethodCall('setOptions', [$options]);

            $container->setDefinition(sprintf('save_manager.%s', strtolower($class)), $definition);

            $objectSaveManagers->addMethodCall('addSaveManager', [$class, new Reference(sprintf('save_manager.%s', strtolower($class)))]);
        }

        $container->setDefinition(ObjectSaveManagers::class, $objectSaveManagers);
    }

    private function registerDuplicationCheckerConfiguration(array $config, ContainerBuilder $container, Loader\YamlFileLoader $loader)
    {
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
            $duplicationBuilder->addMethodCall('setMetadataCache', [new Reference('element_manager.duplication.mapping.cache.symfony')]);
        }
    }

    private function registerDuplicationCheckerMapping(ContainerBuilder $container, array $config, array &$files)
    {
        $fileRecorder = function ($extension, $path) use (&$files) {
            $files['yaml' === $extension ? 'yml' : $extension][] = $path;
        };

        foreach ($container->getParameter('kernel.bundles_metadata') as $bundle) {
            $dirname = $bundle['path'];

            if (
                $container->fileExists($file = $dirname.'/Resources/config/duplication.yaml', false) ||
                $container->fileExists($file = $dirname.'/Resources/config/duplication.yml', false)
            ) {
                $fileRecorder('yml', $file);
            }

            if ($container->fileExists($file = $dirname.'/Resources/config/duplication.xml', false)) {
                $fileRecorder('xml', $file);
            }

            if ($container->fileExists($dir = $dirname.'/Resources/config/duplication', '/^$/')) {
                $this->registerMappingFilesFromDir($dir, $fileRecorder);
            }
        }

        $projectDir = $container->getParameter('kernel.project_dir');
        if ($container->fileExists($dir = $projectDir.'/config/duplication', '/^$/')) {
            $this->registerMappingFilesFromDir($dir, $fileRecorder);
        }

        if (is_array($config['mapping']['paths'])) {
            $this->registerMappingFilesFromConfig($container, $config, $fileRecorder);
        }
    }

    private function registerMappingFilesFromDir($dir, callable $fileRecorder)
    {
        foreach (Finder::create()->followLinks()->files()->in($dir)->name('/\.(xml|ya?ml)$/')->sortByName() as $file) {
            $fileRecorder($file->getExtension(), $file->getRealPath());
        }
    }

    private function registerMappingFilesFromConfig(ContainerBuilder $container, array $config, callable $fileRecorder)
    {
        foreach ($config['mapping']['paths'] as $path) {
            if (is_dir($path)) {
                $this->registerMappingFilesFromDir($path, $fileRecorder);
                $container->addResource(new DirectoryResource($path, '/^$/'));
            } elseif ($container->fileExists($path, false)) {
                if (!preg_match('/\.(xml|ya?ml)$/', $path, $matches)) {
                    throw new \RuntimeException(sprintf('Unsupported mapping type in "%s", supported types is only Yaml.', $path));
                }
                $fileRecorder($matches[1], $path);
            } else {
                throw new \RuntimeException(sprintf('Could not open file or directory "%s".', $path));
            }
        }
    }
}
