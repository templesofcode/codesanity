<?php

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

namespace TemplesOfCode\CodeSanity\Test {

    use TemplesOfCode\CodeSanity\Location\LocalLocation;


    /**
     * Class LocalLocationTest
     * @package TemplesOfCode\CodeSanity\Test
     */
    class LocalLocationTest extends \PHPUnit_Framework_TestCase
    {

        /**
         * isValid()  when the directory property is empty
         */
        public function testIsValidFalse1()
        {
            $location = new LocalLocation('');

            $verdict = $location->isValid();

            $this->assertFalse($verdict);
        }

        /**
         * isValid() fails when the directory does not exist
         */
        public function testIsValidFalse2()
        {
            $location = new LocalLocation('/does/not/exist');
            \TemplesOfCode\CodeSanity\Location\Mocker::$isReadableReturnValue = false;
            $verdict = $location->isValid();
            $this->assertFalse($verdict);
        }

        /**
         * isValid() passes validation, dir exists
         */
        public function testIsValidTrue()
        {
            $location = new LocalLocation('/does/exist');
            \TemplesOfCode\CodeSanity\Location\Mocker::$isReadableReturnValue = true;
            $verdict = $location->isValid();
            $this->assertTrue($verdict);
        }

        /**
         * Test exception when isValid fails during buildRoster() call.
         *
         * @expectedException \InvalidArgumentException
         */
        public function testBuildRosterException()
        {
            $location = new LocalLocation('/does/not/exist');
            \TemplesOfCode\CodeSanity\Location\Mocker::$isReadableReturnValue = false;
            $location->buildRoster();

        }
    }
}