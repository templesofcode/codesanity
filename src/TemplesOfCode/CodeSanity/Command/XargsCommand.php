<?php

namespace TemplesOfCode\CodeSanity\Command;

/**
 * Class XargsCommand
 * @package TemplesOfCode\CodeSanity\Command
 */
class XargsCommand extends ShellCommand
{
    /**
     * @var string
     */
    private static $targetCommand = 'xargs';

    /**
     * XargsCommand constructor.
     * @param string|null $executable
     */
    public function __construct($executable = null)
    {
        if (empty($executable)) {
            $executable = $this->resolveExecutable(self::$targetCommand);
        }

        parent::__construct($executable);
    }
}