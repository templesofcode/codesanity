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
    protected static $maskTemplate = '| %%-%d.%ds | %%-%d.%ds | %%-%d.%ds | %%-%d.%ds |';

    /**
     * @var string
     */
    protected $mask = '';

    /**
     * @var int
     */
    protected  $fileNameSpaceLength = 30;

    /**
     * @var int
     */
    protected  $hashSpaceLength = 40;
    
    /**
     * @var string
     */
    protected $border;

    /**
     * {@inheritdoc}
     */
    protected function init()
    {
        $this->setBorder();
        $this->setMask();
    }

    /**
     *
     */
    protected function setBorder()
    {
        /**
         * Initial constant lengths
         * @var int $borderLength
         */
        $borderLength = 13;
        $borderLength = $borderLength
            + ($this->fileNameSpaceLength * 2)
            + ($this->hashSpaceLength * 2)
        ;

        $this->border = str_repeat('-', $borderLength);

    }

    /**
     *
     */
    protected function setMask()
    {
           $this->mask = sprintf(
               static::$maskTemplate,
               $this->getFileNameSpaceLength(),
               $this->getFileNameSpaceLength(),
               $this->getHashSpaceLength(),
               $this->getHashSpaceLength(),
               $this->getFileNameSpaceLength(),
               $this->getFileNameSpaceLength(),
               $this->getHashSpaceLength(),
               $this->getHashSpaceLength()
           );
    }

    /**
     * @return int
     */
    public function getFileNameSpaceLength()
    {
        return $this->fileNameSpaceLength;
    }

    /**
     * @param int $fileNameSpaceLength
     * @return PrettyOutput
     */
    public function setFileNameSpaceLength($fileNameSpaceLength)
    {
        if ($fileNameSpaceLength == $this->fileNameSpaceLength) {
            return $this;
        }

        $this->fileNameSpaceLength = $fileNameSpaceLength;

        $this->setBorder();
        $this->setMask();

        return $this;
    }

    /**
     * @return int
     */
    public function getHashSpaceLength()
    {
        return $this->hashSpaceLength;
    }

    /**
     * @param int $hashSpaceLength
     * @return PrettyOutput
     */
    public function setHashSpaceLength($hashSpaceLength)
    {
        if ($hashSpaceLength == $this->hashSpaceLength) {
            return $this;
        }

        $this->hashSpaceLength = $hashSpaceLength;

        $this->setBorder();
        $this->setMask();

        return $this;
    }

    /**
     *
     */
    public function write()
    {
        if ($this->headerEnabled) {
            $this->writeHeader();
        }

        $this->output->writeln($this->border);

        foreach ($this->differences as $differenceSet) {
            /**
             * @var ArrayCollection<DiffItem> $differenceSet
             */
            $this->writeDifferenceSet($differenceSet);
        }

        $this->output->writeln($this->border);
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

    /**
     * @param DiffItem $diffItem
     */
    private function writeDiffItem(DiffItem $diffItem)
    {
        $sot = $sotHash = 'Missing';

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
            $sot = realpath($sotName . '/' . $sotFileName);

            $sotHash = $sotRosterItem->getHash();
        }

        $target = $targetHash = 'Missing';

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
            $target = realpath($targetName . '/' . $targetFileName);

            $targetHash = $targetRosterItem->getHash();
        }

        $line = sprintf(
            $this->mask,
            $sot,
            $sotHash,
            $target,
            $targetHash
        );

        $this->output->writeln($line);
    }

    /**
     *
     */
    private function writeHeader()
    {
        $this->output->writeln($this->border);
        $this->output->writeln(sprintf(
            $this->mask,
            static::$header[0],
            static::$header[1],
            static::$header[2],
            static::$header[3]
        ));
    }
}
