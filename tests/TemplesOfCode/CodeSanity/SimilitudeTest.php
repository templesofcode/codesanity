<?php

namespace TemplesOfCode\CodeSanity\Test;


use TemplesOfCode\CodeSanity\Similitude;

/**
 * Class SimilitudeTest
 * @package TemplesOfCode\CodeSanity\Test
 */
class SimilitudeTest extends \PHPUnit_Framework_TestCase
{
    public function testFirst()
    {
        $similitude = new Similitude();
        $this->assertNotNull($similitude);
    }
}
