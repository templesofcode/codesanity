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
        if (!$this->targetLocations->contains($targetLocation)) {
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
        if ($this->targetLocations->contains($targetLocation)) {
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
    private function validateResources()
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
            $validTargetLocations &= $location->isValid();
            if (!$validTargetLocations) {
                break;
            }
        }

        return $validTargetLocations;
    }

    /**
     * @return bool|null
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

        /**
         * @var ArrayCollection<DiffItem> $sotRoster
         */
        $sotRoster = $this->sourceOfTruth->buildRoster();

        $targetRosters = new ArrayCollection();
        foreach ($this->targetLocations as $location) {
            /**
             * @var Location $location
             */

            $targetRoster = $location->buildRoster();
            $targetRosters->add($targetRoster);
        }

        $differences = $this->compareAllRosters($sotRoster, $targetRosters);
        return $differences;
    }

    private function compareAllRosters(ArrayCollection $sotRoster, ArrayCollection $targetRosters)
    {
        $differences = new ArrayCollection();

        foreach ($targetRosters as $roster) {
            /**
             * @var ArrayCollection $difference
             */
            $difference = $this->compareRosters($sotRoster, $roster);
            if ($difference->count()) {
                $differences->add($difference);
            }
        }

        return $differences;
    }


    private function compareRosters(ArrayCollection $sotRoster, ArrayCollection $targetRoster)
    {
        $difference = new ArrayCollection();


        foreach ($sotRoster->toArray() as  $fileName => $diffItem) {
            /**
             * @var DiffItem $diffItem
             */

            if (!$targetRoster->containsKey($fileName)) {
                continue;
            }

            /**
             * @var DiffItem $targetDiffItem
             */
            $targetDiffItem = $targetRoster->get($fileName);

            if ($diffItem->getHash() == $targetDiffItem->getHash()) {
                continue;
            }


            

        }

        return $difference;
    }
}
