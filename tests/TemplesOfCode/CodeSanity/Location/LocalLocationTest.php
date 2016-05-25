<?php



use Doctrine\Common\Collections\ArrayCollection;
use TemplesOfCode\CodeSanity\Location\LocalLocation;
use TemplesOfCode\CodeSanity\Roster;
use TemplesOfCode\CodeSanity\RosterItem;
use TemplesOfCode\Sofa\CommandChain;

include_once(__DIR__.'/../MockSpace.php');

class MockLocalLocation extends LocalLocation
{
    /**
     * @return CommandChain
     */
    public function buildPipeChainedCommandsAccessor()
    {
        return $this->buildPipeChainedCommands();
    }

    /**
     * @return CommandChain
     */
    public function buildSequenceChainCommandsAccessor()
    {
        return $this->buildSequenceChainedCommands();
    }
}



/**
 * Class LocalLocationTest
 * @package TemplesOfCode\CodeSanity\Test
 */
class LocalLocationTest extends \PHPUnit_Framework_TestCase
{

    private static $expectedCommandChain=<<<CHAIN
find . ! -type d ! -type l -print | sed -e 's/[^[:alnum:]]/\\\\&/g' | sort | xargs -n1 sha1sum
CHAIN;


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
     *
     */
    public function testBuildPipeChainedCommands()
    {
        $location = new MockLocalLocation('/dir1/dir2/dir3');
        \TemplesOfCode\CodeSanity\Location\Mocker::$isReadableReturnValue = true;

        /**
         * @var CommandChain $commandChain
         */
        $commandChain = $location->buildPipeChainedCommandsAccessor();

        /**
         * @var string $command
         */
        $command = $commandChain->getCommand();

        $this->assertEquals(
            static::$expectedCommandChain,
            $command
        );
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

    /**
     *
     */
    public function testGetName()
    {
        $location = new LocalLocation('/dir1/dir2/dir3');
        $name = $location->getName();
        $this->assertEquals('/dir1/dir2/dir3', $name);
    }

    public function testBuildSequenceChainedCommands()
    {
        $location = new MockLocalLocation('/dir1/dir2/dir3');

        /**
         * @var CommandChain $commandChain
         */
        $commandChain = $location->buildSequenceChainCommandsAccessor();

        /**
         * @var string $command
         */
        $command = $commandChain->getCommand();

        $this->assertEquals(
          'cd /dir1/dir2/dir3',
            $command
        );
    }

    /**
     * @expectedException \TemplesOfCode\Sofa\Exception\ShellExecutionException
     */
    public function testBuildRosterExceptionThrow()
    {
        $location = new LocalLocation('/dir1/dir2/dir3');
        \TemplesOfCode\CodeSanity\Location\Mocker::$isReadableReturnValue = true;
        \TemplesOfCode\Sofa\Mocker::$exitStatus  = 1;
        \TemplesOfCode\Sofa\Mocker::$output = array(
            'Coerced platform error'
        );
        $location->buildRoster();
    }


    /**
     *
     */
    public function testBuildRoster()
    {
        $location = new LocalLocation('/dir1/dir2/dir3');
        \TemplesOfCode\CodeSanity\Location\Mocker::$isReadableReturnValue = true;
        \TemplesOfCode\Sofa\Mocker::$exitStatus  = 0;
        \TemplesOfCode\Sofa\Mocker::$output = array();

        for ($i = 0; $i < 5; $i++) {
            $mockFile = (string)(str_repeat((string)$i, 5));

            \TemplesOfCode\Sofa\Mocker::$output[] = sprintf(
                '%s %s',
                sha1($mockFile),
                $mockFile
            );

        }

        /**
         * @var Roster $roster
         */
        $roster = $location->buildRoster();

        /**
         * @var ArrayCollection $rosterItems
         */
        $rosterItems = $roster->getRosterItems();
        $i = 0;
        foreach ($rosterItems as $rosterItem) {
            /**
             * @var RosterItem $rosterItem
             */

            $mockFile = (string)(str_repeat((string)$i, 5));
            $i++;
            $this->assertEquals($mockFile, $rosterItem->getRelativeFileName());
            $this->assertEquals(sha1($mockFile), $rosterItem->getHash());
        }

    }
    
}
