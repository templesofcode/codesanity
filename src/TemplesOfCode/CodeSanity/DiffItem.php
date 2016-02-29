<?php


namespace TemplesOfCode\CodeSanity;

/**
 * Class DiffItem
 * @package TemplesOfCode\CodeSanity
 */
class DiffItem
{
    /**
     * @var string
     */
    private $hash;

    /***
     * @var string
     */
    private $relativeFileName;

    /**
     * @return string
     */
    public function getHash()
    {
        return $this->hash;
    }

    /**
     * @param string $hash
     * @return DiffItem
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
     * @return DiffItem
     */
    public function setRelativeFileName($relativeFileName)
    {
        $this->relativeFileName = $relativeFileName;
        return $this;
    }
}