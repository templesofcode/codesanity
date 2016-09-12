<?php

namespace TemplesOfCode\CodeSanity\Test;

use Doctrine\Common\Collections\ArrayCollection;
use TemplesOfCode\CodeSanity\Location;
use TemplesOfCode\CodeSanity\Roster;
use TemplesOfCode\CodeSanity\Location\LocalLocation;

/**
 * Class MockLocation
 * @package TemplesOfCode\CodeSanity\Test
 */
class MockLocation extends LocalLocation
{
    /**
     * @return string
     */
    public function getName()
    {
        return $this->directory;
    }

    /**
     * @return bool
     */
    public function isValid()
    {
        return true;
    }

    /**
     * @return ArrayCollection
     */
    public function buildRoster()
    {
    }
}

/**
 * Class RosterTest
 * @package TemplesOfCode\CodeSanity\Test
 */
class RosterTest extends \PHPUnit_Framework_TestCase
{
    /**
     * 
     */
    public function testRosterProperty()
    {
        $roster = new Roster();

        $rosterList = new ArrayCollection();
        $roster->setRosterItems($rosterList);

        $returnedRosterList = $roster->getRosterItems();

        $this->assertSame($rosterList, $returnedRosterList);
    }

    /**
     *
     */
    public function testLocationProperty()
    {
        $location = new MockLocation('/dir1/dir2/dir3');
        $roster = new Roster();
        $roster->setLocation($location);

        /**
         * @var Location $returnedLocation
         */
        $returnedLocation = $roster->getLocation();
        $this->assertSame($location, $returnedLocation);
    }
}
