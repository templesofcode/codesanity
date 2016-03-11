<?php

namespace TemplesOfCode\CodeSanity\Output;

use Doctrine\Common\Collections\ArrayCollection;
use TemplesOfCode\CodeSanity\DiffItem;
use TemplesOfCode\CodeSanity\Output;
use TemplesOfCode\CodeSanity\RosterItem;

class PrettyOutput extends Output
{
    /**
     * @var string
     */
    protected static $mask = '| %-70.70s | %-45.45s | %-70.70s | %-45.45s |';

    /**
     * @var string
     */
    protected static $border;


    /**
     * {@inheritdoc}
     */
    protected function init()
    {
        static::$border = str_repeat('-', strlen(static::$mask));
    }


    /**
     *
     */
    public function write()
    {

        if ($this->headerEnabled) {
            $this->writeHeader();
        }

        $this->output->writeln(static::$border);

        foreach ($this->differences as $differenceSet) {
            /**
             * @var ArrayCollection<DiffItem> $differenceSet
             */
            $this->writeDifferenceSet($differenceSet);
        }
    }

    private function writeDifferenceSet(ArrayCollection $differenceSet)
    {
        foreach ($differenceSet as $diffItem) {
            /**
             * @var DiffItem $diffItem
             */
            $this->writeDiffItem($diffItem);

        }
    }

    /**
     * @param DiffItem $diffItem
     */
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
        }
        else {
            $sotName = 'Missing';
            $sotFileName = 'Missing';
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
        }
        else {
            $targetName = 'Missing';
            $targetFileName = 'Missing';
        }

        $line = sprintf(
            static::$mask,
            $sotName,
            $sotFileName,
            $targetName,
            $targetFileName
        );

        $this->output->writeln($line);
    }

    /**
     *
     */
    private function writeHeader()
    {
        $this->output->writeln(static::$border);
        $this->output->writeln(sprintf(
            static::$mask,
            static::$header[0],
            static::$header[1],
            static::$header[2],
            static::$header[3]
        ));
    }

}