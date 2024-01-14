<?php

/**
 * This file is part of the funique package.
 *
 * (c) Doug Harple <dharple@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Outsanity\Funique\Model;

use Exception;

/**
 * Describes a file
 */
class ChecksumEntry extends Summable
{
    /**
     * A checksum for the file.
     *
     * @var string
     */
    protected $checksum = null;

    /**
     * The filename for this file
     *
     * @var string
     */
    protected $file;

    /**
     * Constructs a new File
     *
     * @param string $checksum The checksum.
     * @param string $file     The filename.
     */
    public function __construct(string $checksum, string $file)
    {
        $this->checksum = $checksum;
        $this->file = $file;
    }

    /**
     * Returns the full path information
     *
     * @return string
     */
    public function getPath(): string
    {
        return sprintf('CHECKSUM: %s', $this->file);
    }

    /**
     * Returns the relative path of this file.
     *
     * @return string
     */
    public function getRelativePath(): string
    {
        return $this->getPath();
    }

    /**
     * Returns the checksum for the entire file.
     *
     * @param string $checksumAlgorithm The algorithm to use.
     *
     * @return string
     */
    public function getSum(string $checksumAlgorithm): string
    {
        return $this->checksum;
    }
}
