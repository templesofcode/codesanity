<?php

namespace TemplesOfCode\CodeSanity\Output;

use Doctrine\Common\Collections\ArrayCollection;
//use Symfony\Component\Console\Output\OutputInterface;
use TemplesOfCode\CodeSanity\Output;
use TemplesOfCode\CodeSanity\DiffItem;
use TemplesOfCode\CodeSanity\RosterItem;

/**
 * Class CsvOutput
 * @package TemplesOfCode\CodeSanity\Output
 */
class CsvOutput extends Output
{

    /**
     * {@inheritdoc}
     */
    protected function init()
    {

    }

    /**
     *
     */
    public function write()
    {
        if ($this->headerEnabled) {
            $this->writeHeader();
        }

        foreach ($this->differences as $differenceSet) {
            /**
             * @var ArrayCollection<DiffItem> $differenceSet
             */

            $this->writeDifferenceSet($differenceSet);
        }
    }

    /**
     *
     */
    private function writeHeader()
    {
        $this->output->writeln(implode(',', self::$header));
    }

    /**
     * @param ArrayCollection $differenceSet
     */
    private function writeDifferenceSet(ArrayCollection $differenceSet)
    {
        foreach ($differenceSet as $diffItem) {
            /**
             * @var DiffItem $diffItem
             */

            $this->writeDiffItem($diffItem);
        }
    }

    private function writeDiffItem(DiffItem $diffItem)
    {
        /**
         * @var RosterItem $sotRosterItem
         */
        $sotRosterItem = $diffItem->getSotRosterItem();
        if (!empty($sotRosterItem)) {
            /**
             * @var string $sotName
             */
            $sotName = $sotRosterItem->getRoster()->getLocation()->getName();
            $sotFileName = $sotRosterItem->getRelativeFileName();
            $sotItem = $sotName .'/'.$sotFileName;
            $sotHash = $sotRosterItem->getHash();
        }
        else {
            $sotItem = 'Missing';
            $sotHash = 'Missing';
        }

        /**
         * @var RosterItem $targetRosterItem
         */
        $targetRosterItem = $diffItem->getTargetRosterItem();
        if (!empty($targetRosterItem)) {
            /**
             * @var string $targetName
             */
            $targetName = $targetRosterItem->getRoster()->getLocation()->getName();
            $targetFileName = $targetRosterItem->getRelativeFileName();

            $targetItem = $targetName .'/'. $targetFileName;
            $targetHash = $targetRosterItem->getHash();
        }
        else {
            $targetItem = 'Missing';
            $targetHash = 'Missing';
        }

        /**
         * @var string $line
         */
        $line = sprintf(
            '%s,%s,%s,%s',
            $sotItem,
            $sotHash,
            $targetItem,
            $targetHash
        );

        $this->output->writeln($line);
    }
}