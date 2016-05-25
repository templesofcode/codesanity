<?php
namespace TemplesOfCode\Sofa {

    /**
     * Class Mocker
     * @package TemplesOfCode\Sofa
     */
    class Mocker
    {
        public static $exitStatus = 0;
        public static $output = array();
    }

    /**
     * @param string $command
     * @param array $output
     * @param int $returnVal
     */
    function exec($command, &$output, &$returnVal)
    {
        if ($command) {
            /**
             * todo: do something with this later.
             */
        }
        $returnVal = Mocker::$exitStatus;
        $output = Mocker::$output;
    }
}


namespace TemplesOfCode\Sofa\Command {

    /**
     * Class Mocker
     * @package TemplesOfCode\Sofa\Command
     */
    class Mocker
    {
        public static $exitStatus = 0;
        public static $cmdExitStatus = 0;
        public static $output = array();

    }

    /**
     * @param string $command
     * @param array $output
     * @param int $returnVal
     */
    function exec($command, &$output, &$returnVal)
    {
        $parsedCommand = explode(' ', $command);
        if ($parsedCommand[0] == 'which') {
            $output[0] = $parsedCommand[1];
            $returnVal = Mocker::$exitStatus;
        }
        else {
            $returnVal = Mocker::$cmdExitStatus;
            $output = Mocker::$output;
        }

    }
}

namespace TemplesOfCode\CodeSanity\Location {

    /**
     * Class Mocker
     * @package TemplesOfCode\CodeSanity\Location
     */
    class Mocker
    {
        /**
         * @var bool
         */
        public static $isReadableReturnValue = false;
    }

    /**
     * Mock php built-in function
     * @param $directory
     * @return bool
     */
    function is_readable($directory)
    {
        if ($directory) {
            /**
             * todo: do something with this later
             */
        }
        return Mocker::$isReadableReturnValue;
    }
}

namespace TemplesOfCode\CodeSanity\Output {
    function realpath($path)
    {
        return $path;
    }
}
