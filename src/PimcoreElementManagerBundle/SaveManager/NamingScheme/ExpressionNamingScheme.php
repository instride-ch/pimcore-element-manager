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

namespace Instride\Bundle\PimcoreElementManagerBundle\SaveManager\NamingScheme;

use Pimcore\Bundle\CoreBundle\EventListener\Traits\PimcoreContextAwareTrait;
use Pimcore\Http\Request\Resolver\PimcoreContextResolver;
use Pimcore\Model\DataObject\AbstractObject;
use Pimcore\Model\DataObject\Concrete;
use Pimcore\Model\DataObject\Service;
use Symfony\Component\ExpressionLanguage\ExpressionLanguage;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ExpressionNamingScheme implements NamingSchemeInterface
{
    use PimcoreContextAwareTrait;

    public function __construct(
        private readonly ExpressionLanguage $expressionLanguage,
        private readonly RequestStack $requestStack,
        PimcoreContextResolver $contextResolver
    ) {
        $this->setPimcoreContextResolver($contextResolver);
    }

    /**
     * @inheritDoc
     */
    public function apply(Concrete $object, array $options): void
    {
        $optionsResolver = new OptionsResolver();
        $optionsResolver->setDefaults([
            'parent_path' => '/',
            'archive_path' => '/_temp',
            'scheme' => '',
            'auto_prefix_path' => true,
            'skip_path_for_variant' => false,
            'initial_key_mapping' => null,
        ]);
        $optionsResolver->setRequired([
            'parent_path', 'archive_path', 'scheme', 'auto_prefix_path'
        ]);

        $options = $optionsResolver->resolve($options);

        $autoPrefixPath = $options['auto_prefix_path'];
        $parentPath = $object->getPublished() ? $options['parent_path'] : $options['archive_path'];

        $namingScheme = $this->expressionLanguage->evaluate(
            $options['scheme'],
            \array_merge($options, ['object' => $object, 'path' => $parentPath])
        );

        // Map initial key to an object field
        if ($options['initial_key_mapping']) {
            $request = $this->requestStack->getMainRequest();

            if (
                null !== $request &&
                $this->matchesPimcoreContext($request, PimcoreContextResolver::CONTEXT_ADMIN) &&
                $object->getKey() &&
                !$object->getId()
            ) {
                $setter = \sprintf('set%s', \ucfirst($options['initial_key_mapping']));

                if (\method_exists($object, $setter)) {
                    $object->$setter($object->getKey());
                }
            }
        }

        if (\is_array($namingScheme)) {
            $key = $namingScheme[\count($namingScheme) - 1];
            unset($namingScheme[\count($namingScheme) - 1]);

            if ($autoPrefixPath) {
                $parentPath .= '/' . \implode('/', $namingScheme);
            } else {
                $parentPath = '/' . \implode('/', $namingScheme);
            }
        } else {
            $key = $namingScheme;
        }

        if (!$key) {
            $className = \strtolower(\ltrim(\preg_replace(
                '/[A-Z]([A-Z](?![a-z]))*/',
                '_$0',
                $object->getClassName()
            ), '_'));
            $key = uniqid(\sprintf('%s_', $className), true);
        }

        $object->setKey($key);
        $parentPath = $this->correctPath($parentPath);

        if (!$options['skip_path_for_variant'] || $object->getType() !== AbstractObject::OBJECT_TYPE_VARIANT) {
            $object->setParent(Service::createFolderByPath($parentPath));
        }

        $object->setKey(Service::getUniqueKey($object));
    }

    private function correctPath(string $path): string
    {
        return \str_replace('//', '/', $path);
    }
}
