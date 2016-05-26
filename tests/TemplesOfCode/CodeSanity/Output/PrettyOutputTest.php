<?php

namespace TemplesOfCode\CodeSanity\Test;


use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Console\Output\NullOutput;
use TemplesOfCode\CodeSanity\Output\PrettyOutput;
use TemplesOfCode\CodeSanity\DiffFinder;
use TemplesOfCode\CodeSanity\Roster;
use TemplesOfCode\CodeSanity\RosterItem;
use Symfony\Component\Console\Output\BufferedOutput;
use TemplesOfCode\CodeSanity\Location;
use TemplesOfCode\CodeSanity\Location\LocalLocation;
/**
 * Class PrettyOutputTestMockLocation
 * @package TemplesOfCode\CodeSanity\Test
 */
class PrettyOutputTestMockLocation extends LocalLocation
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
        if ($this->roster->getRosterItems()->count()) {
            return $this->roster;
        }

        $rosterItems = new ArrayCollection();

        for ($i = 0; $i < 9; $i++) {
            $item = new RosterItem();
            $mockFile = (string)(str_repeat((string)$i,5));
            $item->setHash(sha1($mockFile));
            $item->setRelativeFileName($mockFile);
            $item->setRoster($this->roster);
            $rosterItems->set($mockFile, $item);
        }

        $this->roster->setRosterItems($rosterItems);
        $this->roster->setLocation($this);
        return $this->roster;
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        if (empty($this->name)) {
            $this->name = $this->getDirectory();
        }

        return $this->name;
    }
}


/**
 * Class MockPrettyOutput
 * @package TemplesOfCode\CodeSanity\Test
 */
class MockPrettyOutput extends PrettyOutput
{
    /**
     * @return string
     */
    public function getBorder()
    {
        return $this->border;
    }
}

/**
 * Class PrettyOutputTestFinder
 * @package TemplesOfCode\CodeSanity\Test
 */
class PrettyOutputTestFinder extends DiffFinder
{
    /**
     * {@inheritdoc}
     */
    protected function validateResources()
    {
        return true;
    }
}

/**
 * Class PrettyOutputTest
 * @package TemplesOfCode\CodeSanity\Test
 */
class PrettyOutputTest extends \PHPUnit_Framework_TestCase
{

    /**
     * @var string
     */
    protected static $expectedBorder =<<<OUT
---------------------------------------------------------------------------------------------------------------------------------------------------------
OUT;

    protected static $expectedDiffs =<<<OUT
---------------------------------------------------------------------------------------------------------------------------------------------------------
| Source of truth Location       | Source of truth File Hash                | Target Location                | Target File Hash                         |
---------------------------------------------------------------------------------------------------------------------------------------------------------
| /dir1/dir2/dir3/22222          | 9o9o9o9o9o                               | /dir1/dir2/dir4/22222          | 1a9b9508b6003b68ddfe03a9c8cbc4bd4388339b |
| /dir1/dir2/dir3/33333          | 403d9917c3e950798601addf7ba82cd3c83f344b | /dir1/dir2/dir4/33333          | 8a8a8a8a8                                |
| /dir1/dir2/dir3/88888          | 9eab102e8f9431bb23016851d11e658e0b20b730 | Missing                        | Missing                                  |
| Missing                        | Missing                                  | /dir1/dir2/dir4/77777          | d559965849921585c1849af03b7a51638700d979 |
---------------------------------------------------------------------------------------------------------------------------------------------------------

OUT;


    protected static  $expectedDiffs2 = <<<OUT
-------------------------------------------------------------------------------------------------------------------------------------------------------------
| Source of truth Location        | Source of truth File Hash                 | Target Location                 | Target File Hash                          |
-------------------------------------------------------------------------------------------------------------------------------------------------------------
| /dir1/dir2/dir3/22222           | 9o9o9o9o9o                                | /dir1/dir2/dir4/22222           | 1a9b9508b6003b68ddfe03a9c8cbc4bd4388339b  |
| /dir1/dir2/dir3/33333           | 403d9917c3e950798601addf7ba82cd3c83f344b  | /dir1/dir2/dir4/33333           | 8a8a8a8a8                                 |
| /dir1/dir2/dir3/88888           | 9eab102e8f9431bb23016851d11e658e0b20b730  | Missing                         | Missing                                   |
| Missing                         | Missing                                   | /dir1/dir2/dir4/77777           | d559965849921585c1849af03b7a51638700d979  |
-------------------------------------------------------------------------------------------------------------------------------------------------------------

OUT;


    /**
     *
     */
    public function testInit()
    {
        $prettyOut = new MockPrettyOutput(new ArrayCollection(), new NullOutput());
        $border = $prettyOut->getBorder();
        $this->assertEquals(self::$expectedBorder, $border);
        
    }

    /**
     *
     */
    public function testWrite()
    {
        $finder = new PrettyOutputTestFinder();

        $sotLocation = new PrettyOutputTestMockLocation('/dir1/dir2/dir3');
        $roster1 = new Roster();
        $sotLocation->setRoster($roster1);
        $finder->setSourceOfTruth($sotLocation);
        $sotLocation->buildRoster();

        $targetLocation1 = new PrettyOutputTestMockLocation('/dir1/dir2/dir4');
        $roster2 = new Roster();
        $targetLocation1->setRoster($roster2);
        $finder->addTargetLocation($targetLocation1);
        $targetLocation1->buildRoster();

        /**
         * Diff where difference between two locations, modify sot
         */

        /**
         * @var RosterItem $item
         */
        $item = $sotLocation
            ->getRoster()
            ->getRosterItems()
            ->get('22222');
        $item->setHash('9o9o9o9o9o');

        $sotLocation
            ->getRoster()
            ->getRosterItems()
            ->set('22222', $item);
        /**
         * Diff where difference between two locations, modify target
         */

        /**
         * @var RosterItem $item2
         */
        $item2 = $targetLocation1
            ->getRoster()
            ->getRosterItems()
            ->get('33333');
        $item2->setHash('8a8a8a8a8');

        $targetLocation1
            ->getRoster()
            ->getRosterItems()
            ->set('33333', $item2);

        /**
         * Diff where missing sot
         */
        $sotLocation
            ->getRoster()
            ->getRosterItems()
            ->remove('77777');

        /**
         * Diff where missing target
         */
        $targetLocation1
            ->getRoster()
            ->getRosterItems()
            ->remove('88888');


        $differences = $finder->find();
        $bufferedOutput = new BufferedOutput();

        $differenceOutput = new MockPrettyOutput(
            $differences,
            $bufferedOutput
        );

        $this->assertEquals(1, $differences->count());

        /**
         * This setup should traverse the code that writes the diffs to  output
         */

        $differenceOutput->setHeaderEnabled(true);
        $differenceOutput->write();
        $content = $bufferedOutput->fetch();

        $this->assertEquals(self::$expectedDiffs, $content);

        /**
         * Update the lengths, and compose the new output
         */

        $differenceOutput->setFileNameSpaceLength(
            $differenceOutput->getFileNameSpaceLength() + 1
        );

        $differenceOutput->setHashSpaceLength(
            $differenceOutput->getHashSpaceLength() + 1
        );

        $differenceOutput->write();
        $content = $bufferedOutput->fetch();

        $this->assertEquals(self::$expectedDiffs2, $content);
        
        /**
         * Attempt to update the length with no new ints
         */
        $differenceOutput->setFileNameSpaceLength(
            $differenceOutput->getFileNameSpaceLength()
        );

        $differenceOutput->setHashSpaceLength(
            $differenceOutput->getHashSpaceLength()
        );

        $differenceOutput->write();
        $content = $bufferedOutput->fetch();

        $this->assertEquals(self::$expectedDiffs2, $content);

    }
}
