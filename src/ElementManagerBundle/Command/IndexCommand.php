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
 * @copyright  Copyright (c) 2016-2020 w-vision AG (https://www.w-vision.ch)
 * @license    https://github.com/w-vision/ImportDefinitions/blob/master/gpl-3.0.txt GNU General Public License version 3 (GPLv3)
 */

namespace Wvision\Bundle\ElementManagerBundle\Command;

use CoreShop\Component\Pimcore\BatchProcessing\BatchListing;
use Pimcore\Model\DataObject\AbstractObject;
use Pimcore\Model\DataObject\Listing;
use Symfony\Component\Console\Helper\ProgressBar;
use Wvision\Bundle\ElementManagerBundle\DuplicateIndex\DuplicateFinderInterface;
use Wvision\Bundle\ElementManagerBundle\DuplicateIndex\DuplicatesIndexWorkerInterface;
use Wvision\Bundle\ElementManagerBundle\Metadata\DuplicatesIndex\MetadataRegistryInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

final class IndexCommand extends Command
{
    /**
     * @var MetadataRegistryInterface
     */
    protected $metadataRegistry;

    /**
     * @var DuplicatesIndexWorkerInterface
     */
    protected $indexWorker;

    /**
     * @var DuplicateFinderInterface
     */
    protected $duplicateFinder;

    /**
     * @var EventDispatcherInterface
     */
    protected $eventDispatcher;

    /**
     * @param MetadataRegistryInterface      $metadataRegistry
     * @param DuplicatesIndexWorkerInterface $indexWorker
     * @param DuplicateFinderInterface       $duplicateFinder
     * @param EventDispatcherInterface       $eventDispatcher
     */
    public function __construct(
        MetadataRegistryInterface $metadataRegistry,
        DuplicatesIndexWorkerInterface $indexWorker,
        DuplicateFinderInterface $duplicateFinder,
        EventDispatcherInterface $eventDispatcher
    ) {
        $this->metadataRegistry = $metadataRegistry;
        $this->indexWorker = $indexWorker;
        $this->duplicateFinder = $duplicateFinder;
        $this->eventDispatcher = $eventDispatcher;

        parent::__construct();
    }

    /**
     * configure command.
     */
    protected function configure(): void
    {
        $this
            ->setName('element_manager:duplicate-index');
    }

    /**
     * Execute command.
     *
     * @param InputInterface  $input
     * @param OutputInterface $output
     *
     * @return int
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        foreach ($this->metadataRegistry->all() as $index) {
            $class = ucfirst($index->getClassName());

            /**
             * @var Listing $list
             */
            $list = '\Pimcore\Model\DataObject\\' . $class . '\Listing';
            $list = new $list();

            $list->setObjectTypes([AbstractObject::OBJECT_TYPE_OBJECT, AbstractObject::OBJECT_TYPE_VARIANT]);
            $perLoop = 10;

            $batchList = new BatchListing($list, $perLoop);

            $output->writeln(sprintf('<info>Processing %s Objects of class "%s"</info>', $batchList->count(), $class));
            $progress = new ProgressBar($output, $batchList->count());
            $progress->setFormat(
                '%current%/%max% [%bar%] %percent:3s%% (%elapsed:6s%/%estimated:-6s%) %memory:6s%: %message%'
            );
            $progress->start();

            foreach ($batchList as $object) {
                $progress->setMessage(sprintf('Index %s (%s)', $object->getFullPath(), $object->getId()));
                $progress->advance();

                $this->indexWorker->updateIndex($index, $object);
            }

            $progress->finish();

            $this->duplicateFinder->findPotentialDuplicate($index);
        }

        $output->writeln('');
        $output->writeln('<info>Done</info>');

        return 0;
    }
}
