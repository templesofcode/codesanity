<?php

namespace TemplesOfCode\CodeSanity\Location;

use TemplesOfCode\CodeSanity\RemoteConnection;
use TemplesOfCode\CodeSanity\Location;
use TemplesOfCode\CodeSanity\Command\ShellCommand;
use TemplesOfCode\CodeSanity\CommandChain;
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
    public function populateRoster()
    {
        // TODO: Implement populateRoster() method.
    }
}