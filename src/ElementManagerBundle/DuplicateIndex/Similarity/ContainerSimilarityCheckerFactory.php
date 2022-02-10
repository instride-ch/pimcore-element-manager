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

namespace Wvision\Bundle\ElementManagerBundle\DuplicateIndex\Similarity;

use InvalidArgumentException;
use Psr\Container\ContainerInterface;
use Webmozart\Assert\Assert;

class ContainerSimilarityCheckerFactory implements SimilarityCheckerFactoryInterface
{
    /**
     * @var ContainerInterface
     */
    private $container;

    /**
     * @var array
     */
    private $dataTransformers;

    /**
     * @param ContainerInterface $container
     */
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
        $this->dataTransformers = [];
    }

    /**
     * {@inheritdoc}
     */
    public function getInstance(string $identifier): SimilarityCheckerInterface
    {
        if (!isset($this->dataTransformers[$identifier])) {
            if ($this->container->has($identifier)) {
                $this->dataTransformers[$identifier] = $this->container->get($identifier);
            } else {
                if (!class_exists($identifier)) {
                    throw new InvalidArgumentException(sprintf('Similarity "%s" does not exist.', $identifier));
                }

                $this->dataTransformers[$identifier] = new $identifier();
            }
        }

        Assert::isInstanceOf($this->dataTransformers[$identifier], SimilarityCheckerInterface::class);

        return $this->dataTransformers[$identifier];
    }
}
