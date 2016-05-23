<?php

namespace TemplesOfCode\CodeSanity\Test;


use Symfony\Component\Console\Output\BufferedOutput;
use \TemplesOfCode\CodeSanity\Output\CsvOutput;
use \Doctrine\Common\Collections\ArrayCollection;
use \Symfony\Component\Console\Output\NullOutput;
//use TemplesOfCode\CodeSanity\DiffItem;
use TemplesOfCode\CodeSanity\RosterItem;
use TemplesOfCode\CodeSanity\Location;
use TemplesOfCode\CodeSanity\Roster;
use TemplesOfCode\CodeSanity\DiffFinder;

/**
 * Class CsvOutputTestFinder
 * @package TemplesOfCode\CodeSanity\Test
 */
class CsvOutputTestFinder extends DiffFinder
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
 * Class CsvOutputTestMockLocation
 */
class CsvOutputTestMockLocation extends Location
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
 * Class MockCsvOutput
 */
class MockCsvOutput extends CsvOutput
{
    public function initCaller()
    {
        parent::init();
    }
}

/**
 * Class CsvOutputTest
 */
class CsvOutputTest extends \PHPUnit_Framework_TestCase
{
    private static $csvExpectedOutput=<<<OUT
Source of truth Location,Source of truth File Hash,Target Location,Target File Hash
/dir1/dir2/dir3/22222,9o9o9o9o9o,/dir1/dir2/dir4/22222,1a9b9508b6003b68ddfe03a9c8cbc4bd4388339b
/dir1/dir2/dir3/33333,403d9917c3e950798601addf7ba82cd3c83f344b,/dir1/dir2/dir4/33333,8a8a8a8a8
/dir1/dir2/dir3/88888,9eab102e8f9431bb23016851d11e658e0b20b730,Missing,Missing
Missing,Missing,/dir1/dir2/dir4/77777,d559965849921585c1849af03b7a51638700d979

OUT;


    public function testInit()
    {
        $differenceOutput = new MockCsvOutput(
            new ArrayCollection(),
            new NullOutput()
        );

        $differenceOutput->initCaller();
    }

    public function testWrite()
    {
        $finder = new CsvOutputTestFinder();

        $sotLocation = new CsvOutputTestMockLocation('/dir1/dir2/dir3');
        $roster1 = new Roster();
        $sotLocation->setRoster($roster1);
        $finder->setSourceOfTruth($sotLocation);
        $sotLocation->buildRoster();

        $targetLocation1 = new CsvOutputTestMockLocation('/dir1/dir2/dir4');
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
        $differenceOutput = new MockCsvOutput(
            $differences,
            $bufferedOutput
        );

        $this->assertEquals(1, $differences->count());

        /**
         * @var ArrayCollection $differenceSet
         */
        $differenceSet = $differences->get(0);
        $this->assertEquals(4, $differenceSet->count());


        /**
         * This setup should traverse the code that writes the diffs to  output
         */

        $differenceOutput->setHeaderEnabled(true);
        $differenceOutput->write();

        $content = $bufferedOutput->fetch();

        $this->assertEquals(self::$csvExpectedOutput, $content);
    }
}
