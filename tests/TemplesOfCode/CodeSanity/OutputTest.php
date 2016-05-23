<?php

namespace TemplesOfCode\CodeSanity\Test;

use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Console\Output\NullOutput;
use TemplesOfCode\CodeSanity\Output;

/**
 * Class MockOutput
 * @package TemplesOfCode\CodeSanity\Test
 */
class MockOutput extends Output
{
    /**
     *
     */
    public function write()
    {
    }

    /**
     *
     */
    protected function init()
    {
    }
}

/**
 * Class OutputTest
 * @package TemplesOfCode\CodeSanity\Test
 */
class OutputTest extends \PHPUnit_Framework_TestCase
{
    /**
     *
     */
    public function testHeaderProperty()
    {
        $differences = new ArrayCollection();
        $outStream = new NullOutput();

        $output = new MockOutput($differences, $outStream);
        $output->setHeaderEnabled(true);
        $isHeaderEnabled = $output->isHeaderEnabled();
        $this->assertTrue($isHeaderEnabled);
    }
}
