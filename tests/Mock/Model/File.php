<?php

namespace Outsanity\Tests\Funique\Mock\Model;

use Outsanity\Funique\Model\File as BaseFile;

/**
 * Describes a mock file.
 */
class File extends BaseFile
{
    /**
     * What to fill files with
     */
    protected $filler = 'A';

    /**
     * Returns a string to use to fill in mock files.
     *
     * @return string
     */
    public function getFiller()
    {
        return $this->filler;
    }

    /**
     * Returns the leading checksum size.
     *
     * @return integer
     */
    public function getLeadingChecksumSize()
    {
        return static::LEADING_CHECKSUM_SIZE;
    }

    /**
     * Sets the string to use to fill in mock files.
     *
     * @param string $filler
     *
     * @return self
     */
    public function setFiller(string $filler): self
    {
        $this->filler = $filler;
        return $this;
    }

    /**
     * Sets the size of this mock file.
     *
     * @return self
     */
    public function setSize(string $size): self
    {
        $this->size = $size;
        return $this;
    }
}
