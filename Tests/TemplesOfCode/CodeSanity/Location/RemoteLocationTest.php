<?php

use TemplesOfCode\CodeSanity\Location\RemoteLocation;
use TemplesOfCode\CodeSanity\RemoteConnection;

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
}

/**
 * Class MockRemoteLocation2
 * @package TemplesOfCode\Sofa
 */
class MockRemoteLocation2 extends RemoteLocation
{
    public static $isValidReturnValue = true;

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
     * @expectedException \InvalidArgumentException
     */
    public function testBuildRosterFailNotValid()
    {
        $remoteDirectory = '/dir1/dir2/dir3';

        $remoteLocation = new MockRemoteLocation2($remoteDirectory);
        MockRemoteLocation2::$isValidReturnValue = false;
        $remoteLocation->buildRoster();
    }
}