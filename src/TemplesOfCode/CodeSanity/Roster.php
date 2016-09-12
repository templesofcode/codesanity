<?php

namespace TemplesOfCode\CodeSanity;

use Doctrine\Common\Collections\ArrayCollection;

/**
 * Class Roster
 * @package TemplesOfCode\CodeSanity
 */
class Roster
{
    /**
     * @var ArrayCollection<RosterItem>
     */
    protected $rosterItems;

    /**
     * @var Location
     */
    protected $location;


    public function __construct()
    {
        $this->rosterItems = new ArrayCollection();
    }

    /**
     * @return ArrayCollection
     */
    public function getRosterItems()
    {
        return $this->rosterItems;
    }

    /**
     * @param ArrayCollection $rosterItems
     * @return Roster
     */
    public function setRosterItems($rosterItems)
    {
        $this->rosterItems = $rosterItems;
        return $this;
    }

    /**
     * @return Location
     */
    public function getLocation()
    {
        return $this->location;
    }

    /**
     * @param Location $location
     * @return Roster
     */
    public function setLocation($location)
    {
        $this->location = $location;
        return $this;
    }
}
