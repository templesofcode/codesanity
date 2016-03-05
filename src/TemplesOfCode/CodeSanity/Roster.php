<?php

namespace TemplesOfCode\CodeSanity;


use Doctrine\Common\Collections\ArrayCollection;

class Roster
{
    /**
     * @var ArrayCollection<DiffItem>
     */
    protected $roster;

    /**
     * @var Location
     */
    protected $location;


    public function __construct()
    {
        $this->roster = new ArrayCollection();
    }

    /**
     * @return ArrayCollection
     */
    public function getRoster()
    {
        return $this->roster;
    }

    /**
     * @param ArrayCollection $roster
     * @return Roster
     */
    public function setRoster($roster)
    {
        $this->roster = $roster;
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