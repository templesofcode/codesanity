<?php


namespace TemplesOfCode\CodeSanity;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class Output
 * @package TemplesOfCode\CodeSanity
 */
abstract class Output
{
    /**
     * @var array
     */
    protected static $header = [
        'Source of truth Location',
        'Source of truth File Hash',
        'Target Location',
        'Target File Hash'
    ];

    /**
     * @var string
     */
    protected $mask = '';

    /**
     * @var bool
     */
    protected $headerEnabled;

    /**
     * @var OutputInterface
     */
    protected  $output;

    /**
     * @var ArrayCollection
     */
    protected $differences;

    /**
     * Output constructor.
     * @param ArrayCollection $differences
     * @param OutputInterface $output
     */
    public function __construct(ArrayCollection $differences, OutputInterface $output)
    {
        $this->differences = $differences;
        $this->output = $output;

        $this->init();
    }

    /**
     * @return boolean
     */
    public function isHeaderEnabled()
    {
        return $this->headerEnabled;
    }

    /**
     * @param boolean $headerEnabled
     * @return $this
     */
    public function setHeaderEnabled($headerEnabled)
    {
        $this->headerEnabled = $headerEnabled;
        return $this;
    }

    /**
     * @return string
     */
    protected function getMask()
    {
        return $this->mask;
    }

    /**
     * @param DiffItem $diffItem
     */
    protected function writeDiffItem(DiffItem $diffItem)
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
            $this->getMask(),
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
    abstract public function write();

    /**
     * Perform any logic at construction time.
     */
    abstract protected function init();
}
