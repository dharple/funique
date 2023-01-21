<?php

/**
 * This file is part of the funique test suite.
 *
 * (c) Doug Harple <dharple@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Outsanity\Tests\Funique\Mock\Model;

use Outsanity\Funique\Model\File as BaseFile;

/**
 * Describes a mock file.
 */
class File extends BaseFile
{
    /**
     * What to fill files with
     *
     * @var string
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
     * @param string $filler What string to use as filler.
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
     * @param int $size The size to set the mock file to.
     *
     * @return self
     */
    public function setSize(int $size): self
    {
        $this->size = $size;
        return $this;
    }
}
