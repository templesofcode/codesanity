<?php
namespace TemplesOfCode\CodeSanity\Command;

/**
 * Interface ChainableCommand
 * @package TemplesOfCode\CodeSanity\Command
 */
interface ChainableCommand
{
    /**
     * @return string
     */
    public function getCommand();

    /**
     * @return mixed
     */
    public function execute();
}