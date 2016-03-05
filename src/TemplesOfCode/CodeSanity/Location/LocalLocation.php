<?php

namespace TemplesOfCode\CodeSanity\Location;

use Doctrine\Common\Collections\ArrayCollection;
use TemplesOfCode\CodeSanity\DiffItem;
use TemplesOfCode\CodeSanity\Exception\ShellExecutionException;
use TemplesOfCode\CodeSanity\Command\FindCommand;
use TemplesOfCode\CodeSanity\Command\SedCommand;
use TemplesOfCode\CodeSanity\Command\SortCommand;
use TemplesOfCode\CodeSanity\Command\Sha1SumCommand;
use TemplesOfCode\CodeSanity\Command\XargsCommand;
use TemplesOfCode\CodeSanity\Command\CdCommand;
use TemplesOfCode\CodeSanity\CommandChain;
use TemplesOfCode\CodeSanity\Location;
use TemplesOfCode\CodeSanity\RosterItem;

/**
 * Class LocalLocation
 * @package TemplesOfCode\CodeSanity
 */
class LocalLocation extends Location
{
    /**
     * {@inheritdoc}
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
        foreach ($output as $line) {
            $hashAndFile = preg_split('/\s+/', $line);

            $item = new RosterItem();
            $item->setHash($hashAndFile[0]);
            $item->setRelativeFileName($hashAndFile[1]);
            $item->setRoster($this->roster);

            $rosterItems->set($hashAndFile[1], $item);
        }

        $this->roster->setRoster($rosterItems);
        $this->roster->setLocation($this);

        return $this->roster;
    }

    /**
     * @return CommandChain
     */
    private function buildPipeChainedCommands()
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
    private function buildSequenceChainedCommands()
    {
        $sequenceChainedCommands = new CommandChain(';');

        $cdCommand = new CdCommand();
        $cdCommand->addParameter($this->getDirectory());
        $sequenceChainedCommands->addCommand($cdCommand);

        return $sequenceChainedCommands;
    }
}