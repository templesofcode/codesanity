<?php

namespace TemplesOfCode\CodeSanity;

use Doctrine\Common\Collections\ArrayCollection;
use TemplesOfCode\CodeSanity\Location\LocalLocation;
use TemplesOfCode\CodeSanity\Location\RemoteLocation;

/**
 * Class DiffFinder
 * @package TemplesOfCode\CodeSanity
 */
class DiffFinder
{
    /**
     * @var string
     */
    protected static $localLocationPattern=<<<PATTERN
/(\/[A-Za-z0-9_\-\.]+)*\/?/
PATTERN;

    /**
     * todo: it'll do for now, but evolve it.
     * @var string
     */
    protected static $remoteLocationPattern = <<<REGEXP
/^\w+@([0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}|(\w+|\.)+)+\:(\/[A-Za-z0-9_\-\.]+)+\/?$/
REGEXP;

    /**
     * @var Location
     */
    protected $sourceOfTruth;

    /**
     * @var ArrayCollection
     */
    protected $targetLocations;

    /**
     * DiffFinder constructor.
     *
     * @param string|null $sourceLocation
     * @param ArrayCollection|null $targets
     */
    public function __construct($sourceLocation = null, ArrayCollection $targets = null)
    {

        $this->targetLocations = new ArrayCollection();
        if (!is_null($sourceLocation)) {
            $this->resolveSourceLocation($sourceLocation);
        }

        if (!is_null($targets) && !$targets->isEmpty()) {
            $this->resolveTargetLocations($targets);
        }
    }

    /**
     * @return ArrayCollection
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
             * @var ArrayCollection $differenceSet
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

        foreach ($sotRoster->getRosterItems()->toArray() as  $fileName => $rosterItem) {

            $fileName = (string)$fileName;

            /**
             * @var RosterItem $rosterItem
             */

            if (!$targetRoster->getRosterItems()->containsKey($fileName)) {
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
            $targetItem = $targetRoster->getRosterItems()->get($fileName);

            /**
             * @var string $targetFilename
             */
            $targetFilename = $targetItem->getRelativeFileName();

            $processedItems->add($targetFilename);

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
        foreach ($targetRoster->getRosterItems()->toArray() as $fileName => $rosterItem) {

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


    /**
     * @param string|null $source
     * @return bool
     */
    protected function resolveSourceLocation($source = null)
    {
        /**
         * @var Location $location
         */
        $location = $this->resolveLocation($source);

        if (!is_null($location)) {
            $this->setSourceOfTruth($location);
        }

        return true;
    }

    /**
     * @param ArrayCollection|null $targets
     * @return bool
     */
    protected function resolveTargetLocations(ArrayCollection  $targets = null)
    {
        foreach ($targets as $target) {
            /**
             * @var string $target
             * @var Location $location
             */
            $location = $this->resolveLocation($target);
            $this->addTargetLocation($location);
        }

        return true;
    }

    /**
     * @param string $location
     * @return  Location|null
     */
    protected function resolveLocation($location)
    {
        $resolvedLocation = null;

        $matches = array();
        if (preg_match(self::$remoteLocationPattern, $location, $matches)) {

            /**
             * @var array $boom1
             */
            $boom1 = explode('@', $location);

            /**
             * @var string $username
             */
            $username = $boom1[0];

            $boom2 = explode(':', $boom1[1]);


            /**
             * @var string $host
             */
            $host = $boom2[0];

            /**
             * @var string $directory
             */
            $directory = $boom2[1];

            /**
             * @var RemoteLocation $resolvedLocation
             */
            $resolvedLocation = $this->buildRemoteLocation($username, $host, $directory);
        }
        else if (preg_match(self::$localLocationPattern, $location)) {
            /**
             * @var LocalLocation $resolvedLocation
             */
            $resolvedLocation = $this->buildLocalLocation($location);
        }

        return $resolvedLocation;
    }

    /**
     *
     * todo: can this be static?
     *
     * @param string $username
     * @param string $host
     * @param string $directory
     * @return RemoteLocation
     */
    protected function buildRemoteLocation($username, $host, $directory)
    {
        $location = new RemoteLocation($directory);
        $options = array(
            'executable' => 'ssh',
            'host' => $host,
            'port' => 22,
            'username' => $username,
        );
        $connection = new RemoteConnection($options);
        $location->setRemoteConnection($connection);
        return $location;

    }

    /**
     * todo: can this be static?
     *
     * @param string $location
     * @return LocalLocation
     */
    protected function buildLocalLocation($location)
    {
        $location = new LocalLocation($location);
        return $location;
    }
}
