<?php

namespace TemplesOfCode\CodeSanity;

use Doctrine\Common\Collections\ArrayCollection;

/**
 * Class DiffFinder
 * @package TemplesOfCode\CodeSanity
 */
class DiffFinder
{
    /**
     * @var Location
     */
    protected $sourceOfTruth;

    /**
     * @var ArrayCollection<Location>
     */
    protected $targetLocations;


    /**
     * DiffFinder constructor.
     */
    public function __construct()
    {
        $this->targetLocations = new ArrayCollection();
    }

    /**
     * @return ArrayCollection<Location>
     */
    public function getTargetLocations()
    {
        return $this->targetLocations;
    }

    /**
     * @param Location $targetLocation
     * @return $this
     */
    public function addTargetLocation(Location $targetLocation)
    {
        /**
         * @var bool $targetLocationPresents
         */
        $targetLocationPresent = $this
            ->targetLocations
            ->contains($targetLocation)
        ;

        if (!$targetLocationPresent) {
            $this->targetLocations->add($targetLocation);
        }

        return $this;
    }

    /**
     * @param Location $targetLocation
     * @return $this
     */
    public function removeTargetLocation(Location $targetLocation)
    {
        /**
         * @var bool $targetLocationPresent
         */
        $targetLocationPresent = $this
            ->targetLocations
            ->contains($targetLocation)
        ;

        if ($targetLocationPresent) {
            $this->targetLocations->removeElement($targetLocation);
        }

        return $this;
    }

    /**
     * @return Location
     */
    public function getSourceOfTruth()
    {
        return $this->sourceOfTruth;
    }

    /**
     * @param Location $sourceOfTruth
     * @return DiffFinder
     */
    public function setSourceOfTruth(Location $sourceOfTruth)
    {
        $this->sourceOfTruth = $sourceOfTruth;
        return $this;
    }

    /**
     * @return bool
     */
    protected function validateResources()
    {
        /**
         * @var bool $validSourceOfTruth
         */
        $validSourceOfTruth = $this->sourceOfTruth->isValid();
        if (!$validSourceOfTruth) {
            return false;
        }

        if ($this->targetLocations->isEmpty()) {
            return false;
        }

        /**
         * @var bool $validTargetLocations
         */
        $validTargetLocations = true;
        foreach ($this->targetLocations->toArray() as $location) {
            /**
             * @var Location $location
             */

            /**
             * @var bool $validTargetLocations
             */
            $validTargetLocations = $validTargetLocations && $location->isValid();
            if (!$validTargetLocations) {
                break;
            }
        }

        return $validTargetLocations;
    }

    /**
     * @return ArrayCollection
     * @throws \Exception
     */
    public function find()
    {
        /**
         * @var bool $resourcesValidated
         */
        $resourcesValidated = $this->validateResources();
        if (!$resourcesValidated) {
            throw new \Exception("Resources needed to find differences not complete");
        }

        $targetRosters = new ArrayCollection();
        foreach ($this->targetLocations as $location) {
            /**
             * @var Location $location
             */

            /**
             * @var Roster $targetRoster
             */
            $targetRoster = $location->buildRoster();
            $targetRosters->add($targetRoster);
        }

        /**
         * @var Roster $sotRoster
         */
        $sotRoster = $this->sourceOfTruth->buildRoster();

        $differences = $this->compareAllRosters($sotRoster, $targetRosters);
        return $differences;
    }

    /**
     * @param Roster $sotRoster
     * @param ArrayCollection $targetRosters
     * @return ArrayCollection
     */
    private function compareAllRosters(Roster $sotRoster, ArrayCollection $targetRosters)
    {
        $differences = new ArrayCollection();

        foreach ($targetRosters as $roster) {
            /**
             * @var ArrayCollection $difference
             */
            $differenceSet = $this->compareRosters($sotRoster, $roster);
            if ($differenceSet->count()) {
                $differences->add($differenceSet);
            }
        }

        return $differences;
    }

    /**
     * @param Roster $sotRoster
     * @param Roster $targetRoster
     * @return ArrayCollection
     */
    private function compareRosters(Roster $sotRoster, Roster $targetRoster)
    {
        $differenceSet = new ArrayCollection();

        $processedItems = new ArrayCollection();

        foreach ($sotRoster->getRoster()->toArray() as  $fileName => $rosterItem) {
            /**
             * @var RosterItem $rosterItem
             */

            if (!$targetRoster->getRoster()->containsKey($fileName)) {
                /**
                 * Target roster missing the source of truth roster item.
                 */
                $difference = new DiffItem();
                $difference->setSotRosterItem($rosterItem);
                $differenceSet->set($fileName, $difference);
                continue;
            }

            /**
             * @var RosterItem $targetItem
             */
            $targetItem = $targetRoster->getRoster()->get($fileName);

            $processedItems->add($targetItem->getRelativeFileName());

            if ($rosterItem->getHash() == $targetItem->getHash()) {
                continue;
            }

            /**
             * Items differ
             */
            $difference = new DiffItem();
            $difference->setSotRosterItem($rosterItem);
            $difference->setTargetRosterItem($targetItem);
            $differenceSet->set($fileName, $difference);
        }

        /**
         * Find the items missing from source of truth.
         */
        foreach ($targetRoster->getRoster()->toArray() as $fileName => $rosterItem) {

            $fileName = (string)$fileName;
            if ($processedItems->contains($fileName)) {
                /**
                 * Already dealt with in previous loop
                 */
                continue;
            }

            /**
             * Source of truth roster missing the target roster item.
             */
            $difference = new DiffItem();
            $difference->setTargetRosterItem($rosterItem);
            $differenceSet->set($fileName, $difference);
        }

        return $differenceSet;
    }
}
