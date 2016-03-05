<?php


namespace TemplesOfCode\CodeSanity;

/**
 * Class RosterItem
 * @package TemplesOfCode\CodeSanity
 */
class RosterItem
{
    /**
     * @var Roster
     */
    protected $roster;

    /**
     * @var string
     */
    private $hash;

    /***
     * @var string
     */
    private $relativeFileName;

    /**
     * @return Roster
     */
    public function getRoster()
    {
        return $this->roster;
    }

    /**
     * @param Roster $roster
     * @return RosterItem
     */
    public function setRoster(Roster $roster)
    {
        $this->roster = $roster;
        return $this;
    }


    /**
     * @return string
     */
    public function getHash()
    {
        return $this->hash;
    }

    /**
     * @param string $hash
     * @return RosterItem
     */
    public function setHash($hash)
    {
        $this->hash = $hash;
        return $this;
    }

    /**
     * @return string
     */
    public function getRelativeFileName()
    {
        return $this->relativeFileName;
    }

    /**
     * @param string $relativeFileName
     * @return RosterItem
     */
    public function setRelativeFileName($relativeFileName)
    {
        $this->relativeFileName = $relativeFileName;
        return $this;
    }
}