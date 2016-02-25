<?php

namespace TemplesOfCode\CodeSanity;

use Doctrine\Common\Collections\ArrayCollection;

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
    
    private function validateResources()
    {
        /**
         * @var bool $validSourceOfTruth
         */
        $validSourceOfTruth = $this->sourceOfTruth->isValid();
        
        /**
         * @var bool $validTargetLocations
         */
        $validTargetLocation = false;
        if (!$this->targetLocations->isEmpty()) {
            $validTargetLocations = true;
            foreach ($this->targetLocations->toArray() as $location) {
             
                /**
                 * @var bool $validTargetLocations
                 */
                $validTargetLocations &= $location->isValid();
                if (!$validTargetLocations) {
                    break;
                }
            }
        }
        
        /**
         * @var bool $verdict
         */
        $verdict = $validSourceOfTruth && $validTargetLocations
        
        return $verdict;
    }
    
    /**
     * @return bool|null
     */
    public function find()
    {
        $resourcesValidated = $this->validateResources();
        if (!$resourcesValidated) {
            throw new Exception("Resources needed to find differences not complete");
            return 1;
        }
    
    }
}
