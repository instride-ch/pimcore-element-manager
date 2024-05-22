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

namespace Instride\Bundle\PimcoreElementManagerBundle\DuplicateIndex\DataTransformer;

use Psr\Container\ContainerInterface;
use Webmozart\Assert\Assert;

class ContainerDataTransformerFactory implements DataTransformerFactoryInterface
{
    private array $dataTransformers = [];

    public function __construct(private readonly ContainerInterface $container) {}

    public function getInstance(string $identifier): DataTransformerInterface
    {
        if (!isset($this->dataTransformers[$identifier])) {
            if ($this->container->has($identifier)) {
                $this->dataTransformers[$identifier] = $this->container->get($identifier);
            } else {
                if (!\class_exists($identifier)) {
                    throw new \InvalidArgumentException(
                        \sprintf('Data Transformer "%s" does not exist.', $identifier)
                    );
                }

                $this->dataTransformers[$identifier] = new $identifier();
            }
        }

        Assert::isInstanceOf($this->dataTransformers[$identifier], DataTransformerInterface::class);

        return $this->dataTransformers[$identifier];
    }
}
