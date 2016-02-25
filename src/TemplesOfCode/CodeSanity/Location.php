<?php

namespace TemplesOfCode\CodeSanity;

use Doctrine\Common\Collections\ArrayCollection;
use TemplesOfCode\CodeSanity\Exception\ShellExecutionException;

/**
 * Class Location
 * @package TemplesOfCode\CodeSanity
 */
class Location
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
     * @return Location
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
     * @return Location
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
     * @return Location
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
     * @return Location
     */
    public function setHashesRosterFileName($hashesRosterFileName)
    {
        $this->hashesRosterFileName = $hashesRosterFileName;
        return $this;
    }

    /**
     * @return bool
     * @throws ShellExecutionException
     */
    public function populateRoster()
    {
        /**
         * todo: explore the OOP approach by iterating through the dir tree with RecursiveDirectoryIterator.
         */

        if (!is_readable($this->directory)) {
            throw new \InvalidArgumentException(sprintf(
                "Could not read the directory '%s' while attempting to populate location roster",
                $this->directory
            ));
        }

        $pipedCommands = new ArrayCollection();
        
        $findCommand = new FindCommand();


        $pipedCommands = [
            'find . ! -type d ! type l -print',
            'sed -e "s/['.$this->getFileEscapeChars().']/\\\\\\&/g"',
            'sort',
            'xargs -n1 sha1sum >>'.$this->getHashesRosterFileName()
        ];

        $script = [
            "cd ".$this->getDirectory(),
            implode(' | ', $pipedCommands)
        ];

        $script = implode('; ', $script);


        /**
         * todo: include
         */
        list(
            $status,
            $output
        ) = $this->executeScript($script);

        if ($status) {
            $shellException = new ShellExecutionException(sprintf(
                "Failed to execute the shell script successfully:\n\t%s",
                $script
            ));

            $shellException->setOutput($output);

            throw $shellException;
        }
        
        return true;
    }

    /**
     * @param string $script
     * @return int
     */
    private function executeScript($script)
    {
        /**
         * Scope in placeholder variables for execution.
         */

        /**
         * @var [] $output
         */
        $output = [];

        /**
         * @var int $returnStatus
         */
        $returnStatus = null;

        exec($script, $output, $returnStatus);

        return [$returnStatus, $output];
    }
}
