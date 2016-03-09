<?php

namespace TemplesOfCode\CodeSanity;

use Doctrine\Common\Collections\ArrayCollection;
use TemplesOfCode\CodeSanity\Exception\ShellExecutionException;

abstract class Location
{
    protected $tempStorage = '/tmp';

    /**
     * @var Roster
     */
    protected $roster = null;

    /**
     * @var string
     */
    protected $directory;

    /**
     * @var string
     */
    protected $name;

    /**
     * @var string
     */
    protected $hashesRosterFileName = 'hashes.roster';

    /**
     * Location constructor.
     * @param string $directory
     */
    public function __construct($directory)
    {
        $this->directory = $directory;
    }

    /**
     * @return Roster
     */
    public function getRoster()
    {
        return $this->roster;
    }

    /**
     * @param Roster $roster
     * @return $this
     */
    public function setRoster(Roster $roster)
    {
        $this->roster = $roster;
        return $this;
    }

    /**
     * @return string
     */
    public function getDirectory()
    {
        return $this->directory;
    }

    /**
     * @param string $directory
     * @return $this
     */
    public function setDirectory($directory)
    {
        $this->directory = $directory;
        return $this;
    }

    /**
     * @return string
     */
    public function getHashesRosterFileName()
    {
        return $this->hashesRosterFileName;
    }

    /**
     * @param string $hashesRosterFileName
     * @return $this
     */
    public function setHashesRosterFileName($hashesRosterFileName)
    {
        $this->hashesRosterFileName = $hashesRosterFileName;
        return $this;
    }

    /**
     * @return bool
     */
    abstract public function isValid();

    /**
     * @return ArrayCollection<DiffItem>
     * @throws ShellExecutionException
     */
    abstract public function buildRoster();


    /**
     * @return string
     */
    abstract public function getName();

}
