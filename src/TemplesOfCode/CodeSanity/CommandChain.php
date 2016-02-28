<?php
namespace TemplesOfCode\CodeSanity;

use Doctrine\Common\Collections\ArrayCollection;
use TemplesOfCode\CodeSanity\Command\ChainableCommand;

/**
 * Class CommandChain
 * @package TemplesOfCode\CodeSanity
 */
class CommandChain implements ChainableCommand
{
    /**
     * @var string
     */
    protected $chainLink = null;

    /**
     * @var ArrayCollection<ChainableCommand>
     */
    protected $commands;

    /**
     * @var string
     */
    protected $chain;


    /**
     * @var bool
     */
    protected $precedence = false;

    /**
     * CommandChain constructor.
     * @param string $chainLink
     */
    public function __construct($chainLink)
    {
        $this->chainLink = $chainLink;
    }

    /**
     * @param ArrayCollection $commands
     * @return $this
     */
    public function setCommands(ArrayCollection $commands)
    {
        $this->commands = $commands;
        return $this;
    }

    /**
     * @param ChainableCommand $command
     * @return $this
     */
    public function addCommand(ChainableCommand $command)
    {
        if (!$this->commands->contains($command)) {
            $this->commands->add($command);
        }

        return $this;
    }

    /**
     * @param ChainableCommand $command
     * @return $this
     */
    public function removeCommand(ChainableCommand $command)
    {
        if ($this->commands->contains($command)) {
            $this->commands->remove($command);
        }
        return $this;
    }

    /**
     * @param bool $precedence
     * @return $this
     */
    public function setPrecedence($precedence)
    {
        $this->precedence = $precedence;
        return $this;
    }

    /**
     * @return bool
     */
    public function getPrecedence()
    {
        return $this->precedence;
	}

    /**
     * @return string
     */
    public function getCommand()
    {
        if (empty($this->chain)) {
            $this->chain = $this->chain();
        }
        return $this->chain;
    }

    /**
     * @return string
     */
    public function chain()
    {
        $commands = [];
        foreach ($this->commands as $command) {
            $commands[] = $command->getCommand();
        }
        $chain = implode($this->chainLink, $commands);

        if ($this->precedence) {
            $chain = "($chain)";
        }

        return $chain;
    }

    /**
     * @param bool $showOutput
     * @return array
     */
    public function execute($showOutput = false)
    {
        $script = $this->chain();

        /**
         * Scope in placeholder variables for execution.
         */

        /**
         * @var [] $output
         */
        $output = [];

        /**
         * @var int $exitStatus
         */
        $exitStatus = null;

        exec($script, $output, $exitStatus);

        $returnedResources = [$exitStatus];
        if ($showOutput) {
            $returnedResources[] = $output;
        }

        return $returnedResources;
    }
}