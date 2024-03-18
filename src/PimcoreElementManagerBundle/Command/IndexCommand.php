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

namespace Instride\Bundle\PimcoreElementManagerBundle\Command;

use CoreShop\Component\Pimcore\BatchProcessing\BatchListing;
use Doctrine\ORM\NonUniqueResultException;
use Pimcore\Model\DataObject\AbstractObject;
use Pimcore\Model\DataObject\Listing;
use Instride\Bundle\PimcoreElementManagerBundle\DuplicateIndex\DuplicateFinderInterface;
use Instride\Bundle\PimcoreElementManagerBundle\DuplicateIndex\DuplicatesIndexWorkerInterface;
use Instride\Bundle\PimcoreElementManagerBundle\Metadata\DuplicatesIndex\MetadataRegistryInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class IndexCommand extends Command
{
    public function __construct(
        protected readonly MetadataRegistryInterface $metadataRegistry,
        protected readonly DuplicatesIndexWorkerInterface $indexWorker,
        protected readonly DuplicateFinderInterface $duplicateFinder,
        protected readonly EventDispatcherInterface $eventDispatcher
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->setName('element_manager:duplicate-index');
    }

    /**
     * @throws NonUniqueResultException|\JsonException
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        foreach ($this->metadataRegistry->all() as $index) {
            $class = \ucfirst($index->getClassName());

            /** @var Listing $list */
            $list = '\Pimcore\Model\DataObject\\' . $class . '\Listing';
            $list = new $list();

            $list->setObjectTypes([AbstractObject::OBJECT_TYPE_OBJECT, AbstractObject::OBJECT_TYPE_VARIANT]);
            $perLoop = 10;

            $batchList = new BatchListing($list, $perLoop);

            $output->writeln(\sprintf('<info>Processing %s Objects of class "%s"</info>', $batchList->count(), $class));
            $progress = new ProgressBar($output, $batchList->count());
            $progress->setFormat(
                '%current%/%max% [%bar%] %percent:3s%% (%elapsed:6s%/%estimated:-6s%) %memory:6s%: %message%'
            );
            $progress->start();

            foreach ($batchList as $object) {
                $progress->setMessage(\sprintf('Index %s (%s)', $object->getFullPath(), $object->getId()));
                $progress->advance();

                $this->indexWorker->updateIndex($index, $object);
            }

            $progress->finish();

            $this->duplicateFinder->findPotentialDuplicate($index);
        }

        $output->writeln('');
        $output->writeln('<info>Done</info>');

        return Command::SUCCESS;
    }
}
