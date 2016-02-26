<?php

namespace TemplesOfCode\CodeSanity;

use Doctrine\Common\Collections\ArrayCollection;
use TemplesOfCode\CodeSanity\Exception\ShellExecutionException;
use TemplesOfCode\CodeSanity\Command\FindCommand;
use TemplesOfCode\CodeSanity\Command\SedCommand;
use TemplesOfCode\CodeSanity\Command\SortCommand;
use TemplesOfCode\CodeSanity\Command\Sha1SumCommand;
use TemplesOfCode\CodeSanity\Command\XargsCommand;
use TemplesOfCode\CodeSanity\Command\CdCommand;

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
     */
    public function isValid()
    {
        /**
         * @var bool $verdict
         */
        $verdict = !empty($this->directory)
            && !is_readable($this->directory)
        ;

        return $verdict;
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

        if (!$this->isValid()) {
            throw new \InvalidArgumentException(sprintf(
                "Location validation failed for Location with directory '%s'",
                $this->directory
            ));
        }

        /**
         * @var CommandChain $pipeChainedCommands
         */
        $pipeChainedCommands = $this->buildPipeChainedCommands();

        /**
         * @var CommandChain $sequenceChainedCommands
         */
        $sequenceChainedCommands = $this->buildSequenceChainedCommands();
        $sequenceChainedCommands->addCommand($pipeChainedCommands);

        list(
            $status,
            $output
        ) = $sequenceChainedCommands->execute(true);

        if ($status) {
            $shellException = new ShellExecutionException(sprintf(
                "Failed to execute the shell script successfully:\n\t%s",
                $sequenceChainedCommands->getCommand()
            ));

            $shellException->setOutput($output);

            throw $shellException;
        }

        return true;
    }

    /**
     * @return CommandChain
     */
    private function buildPipeChainedCommands()
    {
        $pipeChainedCommands = new CommandChain(' | ');

        $findCommand = new FindCommand();
        $findCommand->addParameter('.');
        $findCommand->addParameter('! -type d');
        $findCommand->addParameter('! -type l');
        $findCommand->addParameter('-print');
        $pipeChainedCommands->addCommand($findCommand);

        $sedCommand = new SedCommand();
        $sedCommand->addArgument('e', '"s/['.$this->getFileEscapeChars().']/\\\\\\&/g"');
        $pipeChainedCommands->addCommand($sedCommand);

        $sortCommand = new SortCommand();
        $pipeChainedCommands->addCommand($sortCommand);

        $sha1sumCommand = new Sha1SumCommand();
        $pipeChainedCommands->addCommand($sha1sumCommand);

        $xargsCommand = new XargsCommand();
        $xargsCommand->addArgument('n', 1);
        $xargsCommand->addParameter($sha1sumCommand->getCommand());
        $xargsCommand->addParameter('>> '.$this->hashesRosterFileName);
        $pipeChainedCommands->addCommand($xargsCommand);

        return $pipeChainedCommands;
    }

    /**
     * @return CommandChain
     */
    private function buildSequenceChainedCommands()
    {
        $sequenceChainedCommands = new CommandChain(';');

        $cdCommand = new CdCommand();
        $cdCommand->addParameter($this->getDirectory());
        $sequenceChainedCommands->addCommand($cdCommand);

        return $sequenceChainedCommands;
    }
}
