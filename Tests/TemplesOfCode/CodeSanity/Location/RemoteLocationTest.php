<?php

use Doctrine\Common\Collections\ArrayCollection;
use TemplesOfCode\CodeSanity\Location\RemoteLocation;
use TemplesOfCode\CodeSanity\RemoteConnection;
use TemplesOfCode\CodeSanity\Roster;
use TemplesOfCode\CodeSanity\RosterItem;

/**
 * Class MockRemoteConnection
 * @package TemplesOfCode\Sofa
 */
class MockRemoteConnection extends RemoteConnection
{
    /**
     * @var bool
     */
    public static $validityReturnValue = true;

    /**
     * @return bool
     */
    public function isValid()
    {
        return self::$validityReturnValue;
    }
}

/**
 * Class MockRemoteLocation
 * @package TemplesOfCode\Sofa
 */
class MockRemoteLocation extends RemoteLocation
{
    public static $validRemoteDirectoryReturnValue = true;

    /**
     * @return bool
     */
    public function isValidRemoteDirectory()
    {
        return self::$validRemoteDirectoryReturnValue;
    }

    /**
     * @return \TemplesOfCode\Sofa\CommandChain
     */
    public function buildSequenceChainedCommandsAccessor()
    {
        return $this->buildSequenceChainedCommands();
    }
}

/**
 * Class MockRemoteLocation2
 * @package TemplesOfCode\Sofa
 */
class MockRemoteLocation2 extends RemoteLocation
{
    public static $isValidReturnValue = true;

    /**
     * @return bool
     */
    public function isValid()
    {
        return self::$isValidReturnValue;
    }
}


/**
 * Class RemoteLocationTest
 * @package TemplesOfCode\Sofa
 */
class RemoteLocationTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var string
     */
    private static $standAloneCDCommandChain=<<<CHAIN
cd /dir1/dir2/dir3
CHAIN;

    private static $remoteName=<<<NAME
mockUser@mockHost:/dir1/dir2/dir3
NAME;


    /**
     *
     */
    public function testRemoteConnectionProperty()
    {
        $options = array(
            'executable' => 'ssh',
            'host' => 'mockHost',
            'port' => 22,
            'username' => 'mockUser',
//            'public_key' => '~/.ssh/id_dsa.pub'
        );

        $remoteDirectory = '/dir1/dir2/dir3';

        $remoteConnection = new MockRemoteConnection($options);

        $remoteLocation = new RemoteLocation($remoteDirectory);
        $remoteLocation->setRemoteConnection($remoteConnection);

        $remoteConnection2 = $remoteLocation->getRemoteConnection();
        $this->assertEquals($remoteConnection->getHost(), $remoteConnection2->getHost());
        $this->assertEquals($remoteConnection->getPort(), $remoteConnection2->getPort());
//        $this->assertEquals($remoteConnection->getPublicKey(), $remoteConnection2->getPublicKey());
        $this->assertEquals($remoteConnection2->getUsername(), $remoteConnection2->getUsername());
    }

    /**
     *
     */
    public function testIsValidFailsWhenConnNotValid()
    {
        $options = array(
            'executable' => 'ssh',
            'host' => 'mockHost',
            'port' => 22,
            'username' => 'mockUser',
//            'public_key' => '~/.ssh/id_dsa.pub'
        );

        $remoteDirectory = '/dir1/dir2/dir3';

        $remoteConnection = new MockRemoteConnection($options);

        $remoteLocation = new RemoteLocation($remoteDirectory);
        $remoteLocation->setRemoteConnection($remoteConnection);

        MockRemoteConnection::$validityReturnValue = false;

        $this->assertFalse($remoteLocation->isValid());
    }

    /**
     *
     */
    public function testIsValidFailsWhenNoConn()
    {
        $remoteDirectory = '/dir1/dir2/dir3';
        $remoteLocation = new RemoteLocation($remoteDirectory);
        $verdict = $remoteLocation->isValid();
        $this->assertFalse($verdict);
    }

    /**
     *
     */
    public function testFailValidRemoteDirectory()
    {
        $options = array(
            'executable' => 'ssh',
            'host' => 'mockHost',
            'port' => 22,
            'username' => 'mockUser',
//            'public_key' => '~/.ssh/id_dsa.pub'
        );

        $remoteConnection = new MockRemoteConnection($options);

        $remoteDirectory = '/dir1/dir2/dir3';
        $remoteLocation = new MockRemoteLocation($remoteDirectory);
        $remoteLocation->setRemoteConnection($remoteConnection);
        MockRemoteConnection::$validityReturnValue = true;
        MockRemoteLocation::$validRemoteDirectoryReturnValue = false;
        $verdict = $remoteLocation->isValid();
        $this->assertFalse($verdict);
    }

    /**
     *
     */
    public function testPassIsValid()
    {
        $options = array(
            'executable' => 'ssh',
            'host' => 'mockHost',
            'port' => 22,
            'username' => 'mockUser',
//            'public_key' => '~/.ssh/id_dsa.pub'
        );

        $remoteConnection = new MockRemoteConnection($options);

        $remoteDirectory = '/dir1/dir2/dir3';
        $remoteLocation = new MockRemoteLocation($remoteDirectory);
        $remoteLocation->setRemoteConnection($remoteConnection);
        MockRemoteConnection::$validityReturnValue = true;
        MockRemoteLocation::$validRemoteDirectoryReturnValue = true;
        $verdict = $remoteLocation->isValid();
        $this->assertTrue($verdict);
    }

    /**
     *
     */
    public function testFailIsValidRemoteDirectoryNoConn()
    {
        $remoteDirectory = '/dir1/dir2/dir3';
        $remoteLocation = new RemoteLocation($remoteDirectory);
        $verdict = $remoteLocation->isValidRemoteDirectory();
        $this->assertFalse($verdict);
    }

    /**
     *
     */
    public function testFailIsValidRemoteDirectoryCommandFail()
    {
        $options = array(
            'executable' => 'ssh',
            'host' => 'mockHost',
            'port' => 22,
            'username' => 'mockUser',
//            'public_key' => '~/.ssh/id_dsa.pub'
        );

        $remoteDirectory = '/dir1/dir2/dir3';
        $remoteConnection = new RemoteConnection($options);
        $remoteLocation = new RemoteLocation($remoteDirectory);
        $remoteLocation->setRemoteConnection($remoteConnection);
        \TemplesOfCode\Sofa\Mocker::$exitStatus = 1;
        $verdict = $remoteLocation->isValidRemoteDirectory();
        $this->assertFalse($verdict);
    }

    /**
     *
     */
    public function testPassValidRemoteDirectory()
    {
        $options = array(
            'executable' => 'ssh',
            'host' => 'mockHost',
            'port' => 22,
            'username' => 'mockUser',
//            'public_key' => '~/.ssh/id_dsa.pub'
        );

        $remoteDirectory = '/dir1/dir2/dir3';
        $remoteConnection = new RemoteConnection($options);
        $remoteLocation = new RemoteLocation($remoteDirectory);
        $remoteLocation->setRemoteConnection($remoteConnection);
        \TemplesOfCode\Sofa\Mocker::$exitStatus = 0;
        $verdict = $remoteLocation->isValidRemoteDirectory();
        $this->assertTrue($verdict);
    }

    /**
     *
     */
    public function testBuildSequenceChainedCommand()
    {
        $options = array(
            'executable' => 'ssh',
            'host' => 'mockHost',
            'port' => 22,
            'username' => 'mockUser',
//            'public_key' => '~/.ssh/id_dsa.pub'
        );

        $remoteDirectory = '/dir1/dir2/dir3';
        $remoteConnection = new RemoteConnection($options);
        $remoteLocation = new MockRemoteLocation($remoteDirectory);
        $remoteLocation->setRemoteConnection($remoteConnection);

        $cmdChain = $remoteLocation->buildSequenceChainedCommandsAccessor();
        $chain = $cmdChain->chain();

        $this->assertEquals(self::$standAloneCDCommandChain, $chain);

    }

    /**
     *
     */
    public function testGetName()
    {
        $options = array(
            'executable' => 'ssh',
            'host' => 'mockHost',
            'port' => 22,
            'username' => 'mockUser',
//            'public_key' => '~/.ssh/id_dsa.pub'
        );

        $remoteDirectory = '/dir1/dir2/dir3';
        $remoteConnection = new RemoteConnection($options);
        $remoteLocation = new MockRemoteLocation($remoteDirectory);
        $remoteLocation->setRemoteConnection($remoteConnection);

        $name = $remoteLocation->getName();

        $this->assertEquals(self::$remoteName, $name);

    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testBuildRosterFailNotValid()
    {
        $remoteDirectory = '/dir1/dir2/dir3';

        $remoteLocation = new MockRemoteLocation2($remoteDirectory);
        MockRemoteLocation2::$isValidReturnValue = false;
        $remoteLocation->buildRoster();
    }

    /**
     * @expectedException \TemplesOfCode\Sofa\Exception\ShellExecutionException
     */
    public function testBuildRosterFailSshCmdFail()
    {
        $remoteDirectory = '/dir1/dir2/dir3';
        $options = array(
            'executable' => 'ssh',
            'host' => 'mockHost',
            'port' => 22,
            'username' => 'mockUser',
//            'public_key' => '~/.ssh/id_dsa.pub'
        );

        $remoteConnection = new RemoteConnection($options);
        $remoteLocation = new MockRemoteLocation2($remoteDirectory);
        $remoteLocation->setRemoteConnection($remoteConnection);
        MockRemoteLocation2::$isValidReturnValue = true;
        TemplesOfCode\Sofa\Command\Mocker::$exitStatus = 0;
        TemplesOfCode\Sofa\Command\Mocker::$cmdExitStatus = 1;
        //\TemplesOfCode\Sofa\Command\Mocker::$output = array();
        /**
         * @var Roster $roster
         */
        $remoteLocation->buildRoster();
   }
    /**
     *
     */
    public function testBuildRoster()
    {
        $remoteDirectory = '/dir1/dir2/dir3';
        $options = array(
            'executable' => 'ssh',
            'host' => 'mockHost',
            'port' => 22,
            'username' => 'mockUser',
//            'public_key' => '~/.ssh/id_dsa.pub'
        );

        $remoteConnection = new RemoteConnection($options);
        $remoteLocation = new MockRemoteLocation2($remoteDirectory);
        $remoteLocation->setRemoteConnection($remoteConnection);
        MockRemoteLocation2::$isValidReturnValue = true;
        TemplesOfCode\Sofa\Command\Mocker::$exitStatus = 0;
        TemplesOfCode\Sofa\Command\Mocker::$cmdExitStatus = 0;
        \TemplesOfCode\Sofa\Command\Mocker::$output = array();

        for ($i = 0; $i < 5; $i++) {
            $mockFile = (string)(str_repeat((string)$i, 5));

            \TemplesOfCode\Sofa\Command\Mocker::$output[] = sprintf(
                '%s %s',
                sha1($mockFile),
                $mockFile
            );

        }

        /**
         * @var Roster $roster
         */
        $roster = $remoteLocation->buildRoster();

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