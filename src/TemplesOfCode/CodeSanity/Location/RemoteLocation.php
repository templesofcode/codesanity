<?php

namespace TemplesOfCode\CodeSanity\Location;

use TemplesOfCode\CodeSanity\DiffItem;
use TemplesOfCode\CodeSanity\RemoteConnection;
use TemplesOfCode\CodeSanity\Location;
use TemplesOfCode\CodeSanity\Command\ShellCommand;
use TemplesOfCode\CodeSanity\CommandChain;
use TemplesOfCode\CodeSanity\Exception\ShellExecutionException;
use TemplesOfCode\CodeSanity\Command\FindCommand;
use TemplesOfCode\CodeSanity\Command\SedCommand;
use TemplesOfCode\CodeSanity\Command\XargsCommand;
use TemplesOfCode\CodeSanity\Command\SortCommand;
use TemplesOfCode\CodeSanity\Command\CdCommand;
use TemplesOfCode\CodeSanity\Command\Sha1SumCommand;

/**
 * Class RemoteLocation
 * @package TemplesOfCode\CodeSanity\Location
 */
class RemoteLocation extends Location
{
    /**
     * @var RemoteConnection
     */
    protected $remoteConnection;
    
    /**
     * @return RemoteConnection
     */
    public function getRemoteConnection()
    {
        return $this->remoteConnection;
    }

    /**
     * @param RemoteConnection $remoteConnection
     * @return $this
     */
    public function setRemoteConnection(RemoteConnection $remoteConnection)
    {
        $this->remoteConnection = $remoteConnection;
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function isValid()
    {
        $validRemoteConnection = $this
            ->remoteConnection
            ->isValid()
        ;
        
        if (!$validRemoteConnection) {
            return null;
        }
        /**
         * @var bool $validRemoteLocation
         */
        $validRemoteLocation = $this->isValidRemoteDirectory();
        if (!$validRemoteLocation) {
            return false;
        }

        return true;
    }

    /**
     * @return bool
     */
    public function isValidRemoteDirectory()
    {
        /**
         * @var ShellCommand $sshCommand
         */
        $sshCommand = $this
            ->remoteConnection
            ->getCommand(true);

        $sshCommand->addParameter(sprintf(
            '"test -w %s"'.
            $this->directory
        ));

        $sequencedCommandChain = new CommandChain(';');
        $sequencedCommandChain
            ->addCommand($sshCommand)
        ;

        list(
            $exitStatus
            ) = $sequencedCommandChain->execute(false);

        if ($exitStatus) {
            return false;
        }

        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function buildRoster()
    {
        if (!$this->isValid()) {
            throw new \InvalidArgumentException(sprintf(
                "Remote location validation failed for Location with directory '%s'",
                $this->directory
            ));
        }

        /**
         * @var CommandChain $commandChain
         */
        $commandChain = $this->buildRemoteCommandChain();

        $sshCommand = $this->remoteConnection->getCommand(true);
        $sshCommand->addParameter(sprintf(
            '"%s"',
            $commandChain->getCommand()
        ));

        list(
            $status,
            $output
        ) = $sshCommand->execute(true);

        if ($status) {
            $shellException = new ShellExecutionException(sprintf(
                "Failed to execute the remote shell script successfully:\n\t%s",
                $sshCommand->getCommand()
            ));

            $shellException->setOutput($output);

            throw $shellException;
        }

        foreach ($output as $line) {
            $hashAndFile = preg_split('/\s+/', $line);

            $item = new DiffItem();
            $item->setHash($hashAndFile[0]);
            $item->setRelativeFileName($hashAndFile[1]);
            $this->roster->add($item);
        }

        return $this->roster;

    }

    public function buildRemoteCommandChain()
    {
        /**
         * @var CommandChain $pipeChainedCommands
         */
        $pipeChainedCommands = $this->buildPipeChainedCommands();

        /**
         * @var CommandChain $sequenceChainedCommands
         */
        $sequenceChainedCommands = $this->buildSequenceChainedCommands();
        $sequenceChainedCommands->addCommand($pipeChainedCommands);

        return $sequenceChainedCommands;
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
        $sedCommand->addArgument('e', '"s/[[:alnum:]]/\\\\\\\\\\\\&/g"');
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