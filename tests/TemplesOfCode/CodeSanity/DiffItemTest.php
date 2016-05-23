<?php

namespace TemplesOfCode\CodeSanity\Test;


use TemplesOfCode\CodeSanity\DiffItem;
use TemplesOfCode\CodeSanity\RosterItem;

class DiffItemTest extends \PHPUnit_Framework_TestCase
{

    /**
     *
     */
    public function testSotRosterItemProperty()
    {
        $diffItem = new DiffItem();

        $rosterItem = new RosterItem();

        $diffItem->setSotRosterItem($rosterItem);

        $returnedRosterItem = $diffItem->getSotRosterItem();

        $this->assertSame($rosterItem, $returnedRosterItem);
    }

    /**
     *
     */
    public function testTargetRosterItemProperty()
    {
        $diffItem =  new DiffItem();

        $rosterItem = new RosterItem();

        $diffItem->setTargetRosterItem($rosterItem);

        $returnedRosterItem = $diffItem->getTargetRosterItem();

        $this->assertSame($rosterItem,  $returnedRosterItem);
    }
}
