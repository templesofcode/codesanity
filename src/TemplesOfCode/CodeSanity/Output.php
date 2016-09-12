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
             * @var string $sotHash
             * @var string $sot
             */
            list(
                $sot,
                $sotHash
            ) = $this->getRosterInfo($sotRosterItem);
        }

        $target = $targetHash = 'Missing';

        /**
         * @var RosterItem $targetRosterItem
         */
        $targetRosterItem = $diffItem->getTargetRosterItem();
        if (!empty($targetRosterItem)) {
            /**
             * @var string $targetHash
             * @var string $target
             */
            list(
                $target,
                $targetHash
            ) = $this->getRosterInfo($targetRosterItem);
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
     * @param RosterItem $rosterItem
     * @return array
     */
    private function getRosterInfo(RosterItem $rosterItem)
    {
        /**
         * @var Location $location
         */
        $location = $rosterItem->getRoster()->getLocation();

        /**
         * @var string $path
         */
        $path = $location->getFullPath($rosterItem);

        $hash = $rosterItem->getHash();

        return array(
            $path,
            $hash
        );
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
