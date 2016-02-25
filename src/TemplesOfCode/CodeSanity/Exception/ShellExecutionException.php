<?php

namespace TemplesOfCode\CodeSanity\Exception;


class ShellExecutionException extends \Exception
{
    /**
     * @var []
     */
    protected $output;

    /**
     * @return mixed
     */
    public function getOutput()
    {
        return $this->output;
    }

    /**
     * @param [] $output
     * @return ShellExecutionException
     */
    public function setOutput(array $output)
    {
        $this->output = $output;
        return $this;
    }

}