<?php

namespace TemplesOfCode\CodeSanity\Location;

use TemplesOfCode\CodeSanity\Roster;
use TemplesOfCode\CodeSanity\RosterItem;
use TemplesOfCode\CodeSanity\RemoteConnection;
use TemplesOfCode\CodeSanity\Location;
use TemplesOfCode\Sofa\Command\ShellCommand;
use TemplesOfCode\Sofa\CommandChain;
use TemplesOfCode\Sofa\Exception\ShellExecutionException;
use TemplesOfCode\Sofa\Command\FindCommand;
use TemplesOfCode\Sofa\Command\SedCommand;
use TemplesOfCode\Sofa\Command\XargsCommand;
use TemplesOfCode\Sofa\Command\SortCommand;
use TemplesOfCode\Sofa\Command\CdCommand;
use TemplesOfCode\Sofa\Command\Sha1SumCommand;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * Class RemoteLocation
 * @package TemplesOfCode\CodeSanity\Location
 */
class RemoteLocation extends Location
{
    /**
     * @var RemoteConnection
     */
    protected $remoteConnection = null;

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
        /**
         * @var RemoteConnection|null $remoteConnection
         */
        $remoteConnection = $this->getRemoteConnection();
        if (empty($remoteConnection)) {
            return false;
        }

        /**
         * @var bool $validRemoteConnection
         */
        $validRemoteConnection = $this
            ->remoteConnection
            ->isValid()
        ;
        
        if (!$validRemoteConnection) {
            return false;
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
         * @var RemoteConnection|null $remoteConnection
         */
        $remoteConnection = $this->getRemoteConnection();
        if (empty($remoteConnection)) {
            return false;
        }

        /**
         * @var ShellCommand $sshCommand
         */
        $sshCommand = $remoteConnection->getCommand(true);

        $sshCommand->addParameter(sprintf(
            '"test -w %s"',
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

    protected function buildRemoteCommandChain()
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
            /**
             * @var RemoteConnection
             */
            $remoteConnection = $this->getRemoteConnection();

            /**
             * @var string
             */
            $this->name = sprintf(
                '%s@%s:%s',
                $remoteConnection->getUsername(),
                $remoteConnection->getHost(),
                $this->getDirectory()
            );
        }
        return $this->name;
    }
}
