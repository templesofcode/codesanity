<?php

namespace TemplesOfCode\CodeSanity;

/**
 * Class DiffItem
 * @package TemplesOfCode\CodeSanity
 */
class DiffItem
{
    /**
     * @var RosterItem
     */
    protected $sotRosterItem;

    /**
     * @var RosterItem
     */
    protected $targetRosterItem;

    /**
     * @return RosterItem
     */
    public function getSotRosterItem()
    {
        return $this->sotRosterItem;
    }

    /**
     * @param RosterItem $sotRosterItem
     * @return DiffItem
     */
    public function setSotRosterItem(RosterItem $sotRosterItem)
    {
        $this->sotRosterItem = $sotRosterItem;
        return $this;
    }

    /**
     * @return RosterItem
     */
    public function getTargetRosterItem()
    {
        return $this->targetRosterItem;
    }

    /**
     * @param RosterItem $targetRosterItem
     * @return DiffItem
     */
    public function setTargetRosterItem(RosterItem $targetRosterItem)
    {
        $this->targetRosterItem = $targetRosterItem;
        return $this;
    }

}