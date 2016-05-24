<?php

namespace TemplesOfCode\CodeSanity\Location;

use Doctrine\Common\Collections\ArrayCollection;
use TemplesOfCode\Sofa\Exception\ShellExecutionException;
use TemplesOfCode\Sofa\Command\FindCommand;
use TemplesOfCode\Sofa\Command\SedCommand;
use TemplesOfCode\Sofa\Command\SortCommand;
use TemplesOfCode\Sofa\Command\Sha1SumCommand;
use TemplesOfCode\Sofa\Command\XargsCommand;
use TemplesOfCode\Sofa\Command\CdCommand;
use TemplesOfCode\Sofa\CommandChain;
use TemplesOfCode\CodeSanity\Location;
use TemplesOfCode\CodeSanity\RosterItem;
use TemplesOfCode\CodeSanity\Roster;

/**
 * Class LocalLocation
 * @package TemplesOfCode\CodeSanity
 */
class LocalLocation extends Location
{
    /*
     * @param string $directory
     * @return bool
     */
    protected function isReadable($directory)
    {
        return is_readable($directory);
    }

    /**
     * {@inheritdoc}
     */
    public function isValid()
    {
        /**
         * @var bool $verdict
         */
        $verdict = !empty($this->directory) &&
            $this->isReadable($this->directory)
        ;

        return $verdict;
    }

    /**
     * {@inheritdoc}
     */
    public function buildRoster()
    {
        /**
         * todo: explore the OOP approach by iterating through the dir tree with RecursiveDirectoryIterator.
         */

        if (!$this->isValid()) {
            throw new \InvalidArgumentException(sprintf(
                "Local location validation failed for Location with directory '%s'",
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
        $sedCommand->addArgument('e', '"s/[[:alnum:]]/\\\\\\&/g"');
        $pipeChainedCommands->addCommand($sedCommand);

        $sortCommand = new SortCommand();
        $pipeChainedCommands->addCommand($sortCommand);

        $sha1sumCommand = new Sha1SumCommand();
        $pipeChainedCommands->addCommand($sha1sumCommand);

        $xargsCommand = new XargsCommand();
        $xargsCommand->addArgument('n', 1);
        $xargsCommand->addParameter($sha1sumCommand->getCommand());
        //$xargsCommand->addParameter('>> '.$this->hashesRosterFileName);
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

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        if (empty($this->name)) {
            $this->name = $this->getDirectory();
        }

        return $this->name;
    }
}
