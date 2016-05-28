<?php

namespace TemplesOfCode\CodeSanity\Location;

use TemplesOfCode\CodeSanity\RemoteConnection;
use TemplesOfCode\CodeSanity\Location;
use TemplesOfCode\Sofa\Command\ShellCommand;
use TemplesOfCode\Sofa\CommandChain;

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
         * @var CommandChain $commandChain
         */
        $commandChain = $this->buildTestCommandChain($remoteConnection);

        /**
         * @var int $exitStatus
         */
        list(
            $exitStatus
        ) = $commandChain->execute(false);

        if ($exitStatus) {
            return false;
        }

        return true;
    }

    /**
     * @param RemoteConnection $remoteConnection
     * @return CommandChain
     */
    protected function buildTestCommandChain(RemoteConnection $remoteConnection)
    {
        /**
         * @var ShellCommand $sshCommand
         */
        $sshCommand = $remoteConnection->getCommand(true);

        $sshCommand->addParameter(sprintf(
            '"test -e %s"',
            $this->directory
        ));

        $sequencedCommandChain = new CommandChain(';');
        $sequencedCommandChain
            ->addCommand($sshCommand)
        ;

        return $sequencedCommandChain;
    }

    /**
     * @return CommandChain
     */
    public function getRosterListCommand()
    {
        if (!$this->isValid()) {
            throw new \InvalidArgumentException(sprintf(
                "Remote location validation failed for Location with directory '%s'",
                $this->directory
            ));
        }

        /**
         * @var RemoteConnection $remoteConnection
         */
        $remoteConnection = $this->getRemoteConnection();

        /**
         * @var CommandChain $sshCommandChain
         */
        $sshCommandChain = $this->buildSshCommandChain($remoteConnection);

        return $sshCommandChain;
    }

    /**
     * Auxiliary function.
     *
     * @param RemoteConnection $remoteConnection
     * @return ShellCommand
     */
    protected function buildSshCommandChain(RemoteConnection $remoteConnection)
    {
        /**
         * @var CommandChain $commandChain
         */
        $commandChain = $this->buildRemoteCommandChain();

        $remoteCommand = $commandChain->getCommand();
        $remoteCommand = str_replace('\\','\\\\' , $remoteCommand);

        $sshCommand = $remoteConnection->getCommand(true);
        $sshCommand->addParameter(sprintf(
            '"%s"',
            $remoteCommand
        ));

        return $sshCommand;
    }

    /**
     * Auxiliary function.
     * @return CommandChain
     */
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
