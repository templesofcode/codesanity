<?php

namespace TemplesOfCode\CodeSanity;

use Doctrine\Common\Collections\ArrayCollection;
use TemplesOfCode\Sofa\Exception\ShellExecutionException;
use TemplesOfCode\Sofa\Command\FindCommand;
use TemplesOfCode\Sofa\Command\SedCommand;
use TemplesOfCode\Sofa\Command\SortCommand;
use TemplesOfCode\Sofa\Command\Sha1SumCommand;
use TemplesOfCode\Sofa\Command\XargsCommand;
use TemplesOfCode\Sofa\CommandChain;
use TemplesOfCode\Sofa\Command\CdCommand;

/**
 * Class Location
 * @package TemplesOfCode\CodeSanity
 */
abstract class Location
{
    /**
     * @var string
     */
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
     * @return CommandChain
     */
    abstract protected function getRosterListCommand();

    /**
     * @return string
     */
    abstract public function getName();

    /**
     * {@inheritdoc}
     */
    public function buildRoster()
    {

        /**
         * @var CommandChain $rosterListCommand
         */
        $rosterListCommand =$this->getRosterListCommand();

        list(
            $status,
            $output
        ) = $rosterListCommand->execute(true);

        if ($status) {
            $shellException = new ShellExecutionException(sprintf(
                "Failed to execute the shell script successfully:\n\t%s",
                $rosterListCommand->getCommand()
            ));

            $shellException->setOutput($output);

            throw $shellException;
        }

        $rosterItems = new ArrayCollection();

        $roster = new Roster();
        $roster->setLocation($this);

        foreach ($output as $line) {
            $hashAndFile = preg_split('/\s+/', $line);

            $item = new RosterItem();
            $item->setHash($hashAndFile[0]);
            $item->setRelativeFileName($hashAndFile[1]);
            $item->setRoster($roster);

            $rosterItems->set($hashAndFile[1], $item);
        }

        $roster->setRosterItems($rosterItems);
        $this->setRoster($roster);

        return $this->roster;
    }


    /**
     * @return CommandChain
     */
    protected function buildPipeChainedCommands()
    {
        /**
         * @var string $chainLink
         */
        $chainLink = ' | ';

        $pipeChainedCommands = new CommandChain($chainLink);

        $findCommand = new FindCommand();
        $findCommand->addParameter('.');
        $findCommand->addParameter('! -type d');
        $findCommand->addParameter('! -type l');
        $findCommand->addParameter('-print');
        $pipeChainedCommands->addCommand($findCommand);

        $sedCommand = new SedCommand();
        $sedCommand->addArgument('e', 's/[^[:alnum:]]/\\\\&/g');
        $pipeChainedCommands->addCommand($sedCommand);

        $sortCommand = new SortCommand();
        $pipeChainedCommands->addCommand($sortCommand);

        $sha1sumCommand = new Sha1SumCommand();

        $xargsCommand = new XargsCommand();
        $xargsCommand->addParameter('-n1');
        $xargsCommand->addParameter($sha1sumCommand->getCommand());
        $pipeChainedCommands->addCommand($xargsCommand);

        return $pipeChainedCommands;
    }

    /**
     * @return CommandChain
     */
    protected function buildSequenceChainedCommands()
    {
        $sequenceChainedCommands = new CommandChain(';');

        $cdCommand = new CdCommand();
        $cdCommand->addParameter($this->getDirectory());
        $sequenceChainedCommands->addCommand($cdCommand);

        return $sequenceChainedCommands;
    }


}
