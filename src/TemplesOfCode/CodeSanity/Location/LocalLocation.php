<?php

namespace TemplesOfCode\CodeSanity\Location;

use TemplesOfCode\Sofa\CommandChain;
use TemplesOfCode\CodeSanity\Location;
use TemplesOfCode\CodeSanity\RosterItem;

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
     * @return CommandChain
     */
    protected function getRosterListCommand()
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

    /**
     * @param RosterItem $rosterItem
     * @return  string
     */
    public function getFullPath(RosterItem $rosterItem)
    {
        /**
         * @var string $targetFileName
         */
        $targetFileName = $rosterItem->getRelativeFileName();
        $fullPath = sprintf(
            '%s/%s',
            $this->getName(),
            $targetFileName
        );

        return $fullPath;
    }

}
