<?php
/**
 * Created by PhpStorm.
 * User: omarziranhua
 * Date: 4/9/16
 * Time: 11:39 PM
 */

namespace TemplesOfCode\CodeSanity\Test;


//use SebastianBergmann\Diff\Diff;
use TemplesOfCode\CodeSanity\DiffFinder;
use TemplesOfCode\CodeSanity\Location;
use Doctrine\Common\Collections\ArrayCollection;
use TemplesOfCode\CodeSanity\Roster;
use TemplesOfCode\CodeSanity\RosterItem;

/**
 * Class MockLocationForDiffFinderTest
 * @package TemplesOfCode\CodeSanity\Test
 */
class MockLocationForDiffFinderTest extends Location
{
    /**
     * @return string
     */
    public function getName()
    {
        return $this->directory;
    }

    /**
     * @return bool
     */
    public function isValid()
    {
        return true;
    }

    /**
     * @return ArrayCollection
     */
    public function buildRoster()
    {
    }
}

/**
 * Class MockLocationForDiffFinderTest2
 * @package TemplesOfCode\CodeSanity\Test
 */
class MockLocationForDiffFinderTest2 extends Location
{
    /**
     * @return string
     */
    public function getName()
    {
        return $this->directory;
    }

    /**
     * @return bool
     */
    public function isValid()
    {
        return false;
    }

    /**
     * @return ArrayCollection
     */
    public function buildRoster()
    {
    }
}

/**
 * Class MockFinder
 * @package TemplesOfCode\CodeSanity\Test
 */
class MockFinder extends DiffFinder
{
    /**
     * @return bool
     */
    public function validateResourceCaller()
    {
        return $this->validateResources();
    }


}

class MockFinder2 extends DiffFinder
{
    /**
     * {@inheritdoc}
     */
    protected function validateResources()
    {
        return false;
    }
}

class MockFinder3 extends DiffFinder
{
    /**
     * {@inheritdoc}
     */
    protected function validateResources()
    {
        return true;
    }
}

class MockLocation2 extends Location
{
    /**
     * {@inheritdoc}
     */
    public function isValid()
    {
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function buildRoster()
    {
        $rosterItems = new ArrayCollection();

        for ($i = 0; $i < 5; $i++) {
            $item = new RosterItem();
            $mockFile = str_repeat((string)$i,5);
            $item->setHash(sha1($mockFile));
            $item->setRelativeFileName($mockFile);
            $item->setRoster($this->roster);
            $rosterItems->set($mockFile, $item);
        }

        $this->roster->setRoster($rosterItems);
        $this->roster->setLocation($this);
        return $this->roster;
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return uniqid();
    }
}

/**
 * Class DiffFinderTest
 * @package TemplesOfCode\CodeSanity\Test
 */
class DiffFinderTest extends \PHPUnit_Framework_TestCase
{

    /**
     * 
     */
    public function testTargetLocationsProperty()
    {
        $diffFinder = new DiffFinder();

        /**
         * @var ArrayCollection<Location>
         */
        $targetLocations = $diffFinder->getTargetLocations();

        $this->assertEmpty($targetLocations);

        $mockDir = '/dir1/dir2/dir3';
        $location = new MockLocationForDiffFinderTest($mockDir);
        $diffFinder->addTargetLocation($location);
        $targetLocations = $diffFinder->getTargetLocations();
        $this->assertEquals(1, $targetLocations->count());

        /**
         * An attempt to add it again should be handled gracefully internally
         */
        $diffFinder->addTargetLocation($location);
        $targetLocations = $diffFinder->getTargetLocations();
        $this->assertEquals(1, $targetLocations->count());

        /**
         * Remove a non-existing item
         */
        $mockDir = '/dir1/dir2/dir4';
        $location2 = new MockLocationForDiffFinderTest($mockDir);
        $diffFinder->removeTargetLocation($location2);
        $targetLocations = $diffFinder->getTargetLocations();
        $this->assertEquals(1, $targetLocations->count());

        /**
         * Remove the exiting item
         */
        $diffFinder->removeTargetLocation($location);
        $targetLocations = $diffFinder->getTargetLocations();
        $this->assertEquals(0, $targetLocations->count() );


    }

    /**
     *
     */
    public function testSourceOfTruthProperty()
    {
        $diffFinder = new DiffFinder();

        $mockDir = '/dir1/dir2/dir3';
        $location = new MockLocationForDiffFinderTest($mockDir);

        $diffFinder->setSourceOfTruth($location);

        $returnedSourceOfTruth = $diffFinder->getSourceOfTruth();

        $this->assertSame($location, $returnedSourceOfTruth);
    }


    /**
     *
     */
    public function testValidateResources()
    {
        /**
         * Validation fails when source of truth not valid
         */
        $diffFinder = new MockFinder();

        $mockDir = '/dir1/dir2/dir3';
        $location = new MockLocationForDiffFinderTest2($mockDir);
        $diffFinder->setSourceOfTruth($location);

        /**
         * @var bool $verdict
         */
        $verdict = $diffFinder->validateResourceCaller();

        $this->assertFalse($verdict);


        /**
         * Validation fails when the set of target location is empty
         */
        $location = new MockLocationForDiffFinderTest($mockDir);
        $diffFinder->setSourceOfTruth($location);
        $verdict = $diffFinder->validateResourceCaller();
        $this->assertFalse($verdict);

        /**
         * Validation fails when at least one target location fails validation
         */
        $location1  = new MockLocationForDiffFinderTest($mockDir);
        $diffFinder->addTargetLocation($location1);
        $mockDir2 = '/dir1/dir2/dir4';
        $location2 =  new MockLocationForDiffFinderTest2($mockDir2);
        $diffFinder->addTargetLocation($location2);
        $targetLocations = $diffFinder->getTargetLocations();
        $this->assertEquals(2, $targetLocations->count());

        $verdict = $diffFinder->validateResourceCaller();
        $this->assertFalse($verdict);

        /**
         * Validation passes
         */
        $diffFinder->removeTargetLocation($location2);
        $location2 = new MockLocationForDiffFinderTest($mockDir2);
        $diffFinder->addTargetLocation($location2);
        $targetLocations = $diffFinder->getTargetLocations();
        $this->assertEquals(2, $targetLocations->count());
    }

    /**
     * Call to find() should throw an exception when call
     * to validateResources returns false.
     *
     * @expectedException \Exception
     */
    public function testValidateResourcesExceptionOnFind()
    {
        $finder = new MockFinder2();

        $finder->find();
    }

    public function testFindNoDiffs()
    {
        $finder = new MockFinder3();

        $sotLocation = new MockLocation2('/dir1/dir2/dir3');
        $roster1 = new Roster();
        $sotLocation->setRoster($roster1);
        $finder->setSourceOfTruth($sotLocation);

        $targetLocation = new MockLocation2('/dir1/dir2/dir4');
        $roster2 = new Roster();
        $targetLocation->setRoster($roster2);
        $finder->addTargetLocation($targetLocation);


        $this->assertNotEquals($sotLocation->getDirectory(), $targetLocation->getDirectory());
        $this->assertNotEquals($sotLocation->getName(), $targetLocation->getName());

        $differences = $finder->find();
        
        $this->assertEquals($differences->count(), 0);
        
    }

    public function testFindDiff()
    {

    }

    public function testFindDetectMissingSOTItem()
    {

    }


    public function testFindDetectMissingTargetItem()
    {
        
    }
}
