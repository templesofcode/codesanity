<?php

namespace TemplesOfCode\CodeSanity\Test;

use TemplesOfCode\CodeSanity\Roster;
use TemplesOfCode\CodeSanity\RosterItem;

/**
 * Class RosterItemTest
 * @package TemplesOfCode\CodeSanity\Test
 */
class RosterItemTest extends \PHPUnit_Framework_TestCase
{
    /**
     *
     */
    public function testRosterProperty()
    {
        $roster = new Roster();

        $rosterItem = new RosterItem();
        $rosterItem->setRoster($roster);

        /**
         * @var Roster $returnedRoster
         */
        $returnedRoster = $rosterItem->getRoster();

        $this->assertSame($roster, $returnedRoster);
    }

    /**
     *
     */
    public function testHashProperty()
    {
        $hash = '1a2b3c4d';

        $rosterItem = new RosterItem();
        $rosterItem->setHash($hash);

        /**
         * @var string $returnedHash
         */
        $returnedHash = $rosterItem->getHash();

        $this->assertEquals($hash, $returnedHash);
    }

    /**
     *
     */
    public function testRelativeFileNameProperty()
    {
        $relativeFileName = 'dir1/dir2/dir3/f1.txt';

        $rosterItem = new RosterItem();
        $rosterItem->setRelativeFileName($relativeFileName);

        $returnedRelativeFileName = $rosterItem->getRelativeFileName();

        $this->assertEquals($relativeFileName, $returnedRelativeFileName);
    }
}
