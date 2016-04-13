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
        'Target FileHash'
    ];

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
     *
     */
    abstract public function write();

    /**
     * Perform any logic at construction time.
     */
    abstract protected function init();
}