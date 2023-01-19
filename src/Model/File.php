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
class File extends Summable
{
    /**
     * What algorithm to use for the leading checksum.
     *
     * @var string
     */
    protected const LEADING_CHECKSUM_ALGORITHM = 'adler32';

    /**
     * How large of a chunk to carve off of the file for a leading checksum
     * calculation.
     *
     * @var integer
     */
    protected const LEADING_CHECKSUM_SIZE = 2 * 1024;

    /**
     * A checksum for the file.
     *
     * @var string
     */
    protected $checksum = null;

    /**
     * The algorithm used to calculate the currently stored checksum.
     *
     * @var string
     */
    protected $checksumAlgorithm = null;

    /**
     * Cached device information
     *
     * @var int
     */
    protected $device = null;

    /**
     * The directory that this file lives in
     *
     * @var Directory
     */
    protected $dir;

    /**
     * The filename for this file
     *
     * @var string
     */
    protected $file;

    /**
     * Cached inode information
     *
     * @var int
     */
    protected $inode = null;

    /**
     * A checksum calculated based on the initial few bytes of the file.
     *
     * @var string
     */
    protected $leadingSum = null;

    /**
     * Cached filesize
     *
     * @var int
     */
    protected $size = null;

    /**
     * Constructs a new File
     *
     * @param string    $file The filename.
     * @param Directory $dir  The Directory object where this file lives.
     */
    public function __construct(string $file, Directory $dir)
    {
        $this->file = $file;
        $this->dir = $dir;
    }

    /**
     * Returns the device for this file.
     *
     * @return int
     *
     * @throws Exception
     */
    public function getDevice(): int
    {
        if ($this->device === null) {
            $this->loadStats();
        }

        return $this->device;
    }

    /**
     * Returns the inode for this file.
     *
     * @return int
     *
     * @throws Exception
     */
    public function getInode(): int
    {
        if ($this->inode === null) {
            $this->loadStats();
        }

        return $this->inode;
    }

    /**
     * Returns the sum of the first n bytes.
     *
     * @return string
     *
     * @throws Exception
     */
    public function getLeadingSum(): string
    {
        if ($this->leadingSum !== null) {
            return $this->leadingSum;
        }

        $fp = fopen($this->getPath(), 'rb');
        if ($fp === false) {
            throw new Exception(sprintf('Unable to read %s', $this->getPath()));
        }
        $header = fread($fp, static::LEADING_CHECKSUM_SIZE);
        fclose($fp);

        return $this->leadingSum = hash(static::LEADING_CHECKSUM_ALGORITHM, $header);
    }

    /**
     * Returns the full path information
     *
     * @return string
     */
    public function getPath(): string
    {
        return $this->dir->getPath() . '/' . $this->file;
    }

    /**
     * Returns the relative path of this file.
     *
     * @return string
     */
    public function getRelativePath(): string
    {
        return (!($this->dir instanceof BaseDirectory)) ? $this->dir->getRelativePath() . '/' . $this->file : $this->file;
    }

    /**
     * Returns the file size
     *
     * @return int
     *
     * @throws Exception
     */
    public function getSize(): int
    {
        if ($this->size === null) {
            $this->loadStats();
        }

        return $this->size;
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
        if ($this->checksum !== null && $this->checksumAlgorithm === $checksumAlgorithm) {
            return $this->checksum;
        }

        $this->checksumAlgorithm = $checksumAlgorithm;

        return $this->checksum = hash_file($checksumAlgorithm, $this->getPath());
    }

    /**
     * Determines whether or not this file is hard linked to another file.
     *
     * @param File $other The other file to review.
     *
     * @return bool
     *
     * @throws Exception
     */
    public function isHardlinkOf(File $other): bool
    {
        return ($this->getInode() == $other->getInode() && $this->getDevice() == $other->getDevice());
    }

    /**
     * Loads the results of a stat() call into appropriate variables.
     *
     * @return void
     *
     * @throws Exception
     */
    protected function loadStats()
    {
        if ($this->inode !== null) {
            return;
        }

        $stat = @stat($this->getPath());
        if ($stat === false) {
            throw new Exception(sprintf('Unable to stat %s', $this->getPath()));
        }

        $this->device = $stat['dev'];
        $this->inode = $stat['ino'];
        $this->size = $stat['size'];
    }
}
