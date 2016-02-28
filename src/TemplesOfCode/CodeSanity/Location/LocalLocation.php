<?php

namespace TemplesOfCode\CodeSanity\Location;

use TemplesOfCode\CodeSanity\Exception\ShellExecutionException;
use TemplesOfCode\CodeSanity\Command\FindCommand;
use TemplesOfCode\CodeSanity\Command\SedCommand;
use TemplesOfCode\CodeSanity\Command\SortCommand;
use TemplesOfCode\CodeSanity\Command\Sha1SumCommand;
use TemplesOfCode\CodeSanity\Command\XargsCommand;
use TemplesOfCode\CodeSanity\Command\CdCommand;
use TemplesOfCode\CodeSanity\CommandChain;
use TemplesOfCode\CodeSanity\Location;

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
