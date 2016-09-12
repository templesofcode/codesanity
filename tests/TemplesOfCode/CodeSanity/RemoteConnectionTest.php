<?php

namespace TemplesOfCode\CodeSanity\Test;

use TemplesOfCode\Sofa\Command\ShellCommand;
use TemplesOfCode\CodeSanity\RemoteConnection;

/**
 * Class MockRemoteConnection1
 * @package TemplesOfCode\CodeSanity\Test
 */
class MockRemoteConnection1 extends RemoteConnection
{
    /**
     * A connection attempt will fail.
     *
     * @return bool
     */
    protected function testConnection()
    {
        return false;
    }
}

/**
 * Class MockRemoteConnection2
 * @package TemplesOfCode\CodeSanity\Test
 */
class MockRemoteConnection2 extends RemoteConnection
{
    /**
     * A connection attempt will succeed.
     *
     * @return bool
     */
    protected function testConnection()
    {
        return true;
    }
}

/**
 * Class MockShellCommand1
 * @package TemplesOfCode\CodeSanity\Test
 */
class MockShellCommand1 extends ShellCommand
{
    /**
     * Execution will succeed.
     *
     * @return array
     */
    public function execute()
    {
        return array(0,'');
    }
}

/**
 * Class MockShellCommand2
 * @package TemplesOfCode\CodeSanity\Test
 */
class MockShellCommand2 extends ShellCommand
{
    /**
     * Execution will fail.
     *
     * @return array
     */
    public function execute()
    {
        return array(1, '');
    }
}

class MockShellCommand3 extends ShellCommand
{
    /**
     * Execution will succeed
     */
    public function execute()
    {
        return array(0,'');
    }
}

/**
 * Class MockRemoteConnection3
 * @package TemplesOfCode\CodeSanity\Test
 */
class MockRemoteConnection3 extends RemoteConnection
{
    /**
     * {@inheritdoc}
     */
    public function getCommand($hostConnection = true)
    {
        return new MockShellCommand1(':');
    }

    /**
     * {@inheritdoc}
     */
    public function testConnection()
    {
        return parent::testConnection();
    }
}

/**
 * Class MockRemoteConnection4
 * @package TemplesOfCode\CodeSanity\Test
 */
class MockRemoteConnection4 extends RemoteConnection
{
    /**
     * {@inheritdoc}
     */
    public function getCommand($hostConnection = true)
    {
        return new MockShellCommand2(':');
    }

    /**
     * {@inheritdoc}
     */
    public function testConnection()
    {
        return parent::testConnection();
    }
}

class MockRemoteConnection5 extends RemoteConnection
{
    /**
     * @param $publicKey
     * @return $this
     */
    public function setPublicKey($publicKey)
    {
        $this->publicKey = $publicKey;
        return $this;
    }
}

/**
 * Class MockRemoteConnection4
 * @package TemplesOfCode\CodeSanity\Test
 */
class MockRemoteConnection6 extends RemoteConnection
{
    /**
     * {@inheritdoc}
     */
    public function getCommand($hostConnection = true)
    {
        return new MockShellCommand3(':');
    }

    /**
     * {@inheritdoc}
     */
    public function testConnection()
    {
        return parent::testConnection();
    }
}

/**
 * Class MockRemoteConnection7
 * @package TemplesOfCode\CodeSanity\Test
 */
class MockRemoteConnection7 extends RemoteConnection
{
    /**
     * @return ShellCommand
     */
    public function buildSshCommandAccessor()
    {
        return $this->buildSshCommand();
    }
}

/**
 * Class RemoteConnectionTest
 * @package TemplesOfCode\CodeSanity\Test
 */
class RemoteConnectionTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var string
     */
    private static $fqcn =<<<FQCN
TemplesOfCode\Sofa\Command\ShellCommand
FQCN;

    private static $sshTestCommand=<<<CMD
ssh -q remoteUser@remoteHost exit
CMD;

    /**
     * @var array
     */
    private static $defaultOptions = array(
        'username' => 'remoteUser',
        'host' => 'remoteHost',
    );

    /**
     * Throw an exception when the username option is missing.
     *
     * @expectedException \InvalidArgumentException
     */
    public function testGetCommandExceptionThrow1()
    {
        $options = array(
            'host' => 'remoteHost',
        );

        $remoteConnection = new RemoteConnection($options);
        $remoteConnection->getCommand();
    }

    /**
     * Throw an exception when the host is missing.
     *
     * @expectedException  \InvalidArgumentException
     */
    public function testGetCommandExceptionThrow2()
    {
        $options = array(
            'username' => 'remoteUser',
        );

        $remoteConnection = new RemoteConnection($options);
        $remoteConnection->getCommand();
    }

    /**
     * Get command with standard config
     */
    public function testGetCommand()
    {
        $options = array(
            'username' => 'remoteUser',
            'host' => 'remoteHost',
        );

        $remoteConnection = new RemoteConnection($options);

        /**
         * @var ShellCommand $command
         */
        $command = $remoteConnection->getCommand();

        $this->assertInstanceOf(
            static::$fqcn,
            $command,
            '->getCommand() should return an instance of '.static::$fqcn
        );
    }

    /**
     * Add port to options, should still get the command object
     */
    public function testGetCommandWithNonStandardPort()
    {
        $options = array(
            'username' => 'remoteUser',
            'host' => 'remoteHost',
            'port' => 2222
        );

        $remoteConnection = new RemoteConnection($options);

        $this->assertEquals($options['port'], $remoteConnection->getPort());

        /**
         * @var ShellCommand $command
         */
        $command = $remoteConnection->getCommand();

        $this->assertInstanceOf(
            static::$fqcn,
            $command,
            '->getCommand() should return an instance of '.static::$fqcn
        );
    }

    public function testGetCommandWithPublicKey()
    {
        $options = array(
            'username' => 'remoteUser',
            'host' => 'remoteHost',
            'public_key' => '~/.ssh/id_dsa.pub'
        );

        $remoteConnection = new MockRemoteConnection5($options);

        $this->assertEquals($options['public_key'], $remoteConnection->getPublicKey());

        /**
         * @var ShellCommand $command
         */
        $command = $remoteConnection->getCommand();

        $this->assertInstanceOf(
            static::$fqcn,
            $command,
            '->getCommand() should return an instance of '.static::$fqcn
        );
    }

    /**
     *
     */
    public function testIsValidFail()
    {
        $remoteConnection = new MockRemoteConnection1(static::$defaultOptions);
        $verdict = $remoteConnection->isValid();
        $this->assertFalse($verdict);
    }

    /**
     *
     */
    public function testIsValidSuccess()
    {

        $remoteConnection = new MockRemoteConnection2(static::$defaultOptions);
        $verdict = $remoteConnection->isValid();
        $this->assertTrue($verdict);
    }

    /**
     *
     */
    public function testTestConnectionFail()
    {
        $remoteConnection = new MockRemoteConnection4(static::$defaultOptions);
        $verdict = $remoteConnection->testConnection();
        $this->assertFalse($verdict);

    }

    /**
     *
     */
    public function testTestConnectionSuccess()
    {
        $remoteConnection = new MockRemoteConnection6(static::$defaultOptions);
        $verdict = $remoteConnection->testConnection();
        $this->assertTrue($verdict);
    }


    public function testBuildSshCommand()
    {
        $remoteConnection = new MockRemoteConnection7(static::$defaultOptions);
        /**
         * @var ShellCommand $sshCommand;
         */
        $sshCommand = $remoteConnection->buildSshCommandAccessor();
        $this->assertEquals(self::$sshTestCommand, $sshCommand->getCommand());
    }
}
