<?php

namespace TemplesOfCode\CodeSanity;

use AFM\Rsync\SSH;
use TemplesOfCode\CodeSanity\Command\ShellCommand;

/**
 * Class RemoteConnection
 * @package TemplesOfCode\CodeSanity
 */
class RemoteConnection extends SSH
{

    /**
     * RemoteConnection constructor.
     * @param array $options
     */
    public function __construct(array $options)
    {
        parent::__construct($options);
    }

    /**
     * @param bool $hostConnection
     * @return ShellCommand
     */
    public function getCommand($hostConnection = true)
    {
        if(is_null($this->username))
            throw new \InvalidArgumentException("You must specify a SSH username");

        if(is_null($this->host))
            throw new \InvalidArgumentException("You must specify a SSH host to connect");

        $command = new ShellCommand($this->executable);

        if($this->port != 22)
            $command->addArgument("p", $this->port);

        if(!is_null($this->publicKey))
            $command->addArgument("i", $this->publicKey);

        if($hostConnection)
            $command->addParameter($this->getHostConnection());

        return $command;
    }

    /**
     * @return bool
     */
    public function isValid()
    {
        /**
         * @var bool $success
         */
        $success = $this->testConnection();
        if (!$success) {
            return false;
        }

        return true;
    }


    /**
     * @var bool
     * @return bool
     */
    private function testConnection()
    {
        /**
         * @var ShellCommand $sshCommand
         */
        $sshCommand = $this->getCommand(true);
        $sshCommand->addOption('q');
        $sshCommand->addParameter('exit');

        list(
            $exitStatus
        ) = $sshCommand->execute(false);

        if ($exitStatus) {
            return false;
        }

        return true;
    }
}
