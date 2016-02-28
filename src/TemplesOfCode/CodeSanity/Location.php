<?php

namespace TemplesOfCode\CodeSanity;

use Doctrine\Common\Collections\ArrayCollection;
use TemplesOfCode\CodeSanity\Exception\ShellExecutionException;

abstract class Location
{
    /**
     * @var string
     */
    protected $fileEscapeChars = ' ()&';

    /**
     * @var ArrayCollection<HashFile>
     */
    protected $roster = null;

    /**
     * @var string
     */
    protected $directory;

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
        $this->roster = new ArrayCollection();
    }

    /**
     * @return string
     */
    public function getFileEscapeChars()
    {
        return $this->fileEscapeChars;
    }

    /**
     * @param string $char
     * @return $this
     */
    public function addFileEscapeChars($char)
    {
        /**
         * @var int $len
         */
        $len = strlen($char);

        if ($len != 1) {
            throw new \InvalidArgumentException(sprintf(
                "Argument is not a character (string length of 1) for argument '%s' of length %d",
                $char,
                $len
            ));
        }

        /**
         * @var bool $notInList
         */
        $notInList = strpos($this->fileEscapeChars, $char) === false;
        if ($notInList) {
            $this->fileEscapeChars .= $char;
        }

        return $this;
    }

    /**
     * @return ArrayCollection
     */
    public function getRoster()
    {
        return $this->roster;
    }

    /**
     * @param ArrayCollection $roster
     * @return $this
     */
    public function setRoster($roster)
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
     * @return bool
     * @throws ShellExecutionException
     */
    abstract public function populateRoster();
}