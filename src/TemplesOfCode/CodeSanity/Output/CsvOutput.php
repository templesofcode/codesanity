<?php

namespace TemplesOfCode\CodeSanity\Output;

use Doctrine\Common\Collections\ArrayCollection;
use TemplesOfCode\CodeSanity\Output;
use TemplesOfCode\CodeSanity\DiffItem;

/**
 * Class CsvOutput
 * @package TemplesOfCode\CodeSanity\Output
 */
class CsvOutput extends Output
{

    protected $mask = '%s,%s,%s,%s';

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
             * @var ArrayCollection $differenceSet
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
}
