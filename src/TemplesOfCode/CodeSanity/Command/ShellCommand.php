<?php

namespace TemplesOfCode\CodeSanity\Command;

use AFM\Rsync\Command as BaseCommand;
use TemplesOfCode\CodeSanity\Exception\CommandNotFoundException;

/**
 * Class ShellCommand
 * @package TemplesOfCode\CodeSanity\Command
 */
class ShellCommand extends BaseCommand implements ChainableCommand
{
    /**
     * @param $targetCommand
     * @return null|string
     * @throws CommandNotFoundException
     */
    protected function resolveExecutable($targetCommand)
    {
        /**
         * @var string $locateCommand
         */
        $locateCommand = "which " . $targetCommand;

        /**
         * @var string|null $executable
         */
        $executable = `$locateCommand`;
        if (empty($executable)) {
            throw new CommandNotFoundException(sprintf(
                "No '%s' command found in system",
                $targetCommand
            ));
        }
        return $executable;
    }
}