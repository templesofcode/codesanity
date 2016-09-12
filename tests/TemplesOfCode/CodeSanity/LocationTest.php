<?php

namespace TemplesOfCode\CodeSanity\Test;

use TemplesOfCode\CodeSanity\Location;
use TemplesOfCode\CodeSanity\Roster;
use Doctrine\Common\Collections\ArrayCollection;
use TemplesOfCode\CodeSanity\Location\LocalLocation;

/**
 * Class MockLocation
 * @package TemplesOfCode\CodeSanity\Test
 */
class MockLocationForLocationTest extends LocalLocation
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
 * Class LocationTest
 * @package TemplesOfCode\CodeSanity\Test
 */
class LocationTest extends \PHPUnit_Framework_TestCase
{

    /**
     *
     */
    public function testRosterProperty()
    {
        $roster = new Roster();

        $location = new MockLocationForLocationTest('/dir1/dir2/dir3');
        $location->setRoster($roster);
        $returnedRoster = $location->getRoster();
        $this->assertSame($roster, $returnedRoster);
    }

    /**
     *
     */
    public function testDirectoryProperty()
    {
        $mockDir = '/dir1/dir2/dir3';

        $location = new MockLocationForLocationTest($mockDir);

        /**
         * @var string $returnedDir
         */
        $returnedDir = $location->getDirectory();

        $this->assertEquals($mockDir, $returnedDir);

        $mockDir = '/dir1/dir2/dir4';
        $location->setDirectory($mockDir);
        $returnedDir = $location->getDirectory();
        $this->assertEquals($mockDir, $returnedDir);
    }


    public function testHashRosterFileNameProperty()
    {
        $mockDir = '/dir1/dir2/dir3';

        $location = new MockLocationForLocationTest($mockDir);

        $mockHashFileName = 'mock.roster';

        $location->setHashesRosterFileName($mockHashFileName);

        $returnedMockHashFileName = $location->getHashesRosterFileName();

        $this->assertEquals($mockHashFileName, $returnedMockHashFileName);
    }
}
