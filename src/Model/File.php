<?php

/**
 * This file is part of the funique package.
 *
 * (c) Doug Harple <dharple@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Funique\Model;

/**
 * Describes a file
 */
class File extends Entry
{
    protected const FULL_CHECKSUM_ALGORITHM = 'sha512';
    protected const LEADING_CHECKSUM_ALGORITHM = 'adler32';
    protected const LEADING_CHECKSUM_MINIMUM_FILESIZE = 128 * 1024;
    protected const LEADING_CHECKSUM_SIZE = 2 * 1024;

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
     * Whether or not this file is unique.
     *
     * @var boolean
     */
    protected $isUnique = true;

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
     * A checksum for the file.
     *
     * @var string
     */
    protected $sum = null;

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
     * @throws \Exception
     */
    public function getLeadingSum(): string
    {
        if ($this->leadingSum !== null) {
            return $this->leadingSum;
        }

        $fp = fopen($this->getPath(), 'rb');
        if ($fp === false) {
            throw new \Exception('Unable to read ' . $this->getPath());
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
     * Returns the file size
     *
     * @return int
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
     * @return string
     */
    public function getSum(): string
    {
        if ($this->sum !== null) {
            return $this->sum;
        }

        return $this->sum = hash_file(static::FULL_CHECKSUM_ALGORITHM, $this->getPath());
    }

    /**
     * Determines whether or not this file is hardlinked to another file.
     *
     * @param File $other The other file to review.
     *
     * @return bool
     */
    public function isHardlinkOf(File $other): bool
    {
        return ($this->getInode() == $other->getInode() && $this->getDevice() == $other->getDevice());
    }

    /**
     * Determines whether or not this file has the same contents as another
     * file.
     *
     * @param File $other The other file to review.
     *
     * @return bool
     */
    public function isSameAs(File $other): bool
    {
        if ($this->getSize() != $other->getSize()) {
            return false;
        }

        if ($this->getSize() > static::LEADING_CHECKSUM_MINIMUM_FILESIZE) {
            if ($this->getLeadingSum() != $other->getLeadingSum()) {
                return false;
            }
        }

        return ($this->getSum() == $other->getSum());
    }

    /**
     * Whether or not the file is unique.  Defaults to true.
     *
     * @param ?bool $isUnique Pass to set, leave off to return.
     *
     * @return bool
     */
    public function isUnique(?bool $isUnique = null): bool
    {
        if ($isUnique === null) {
            return $this->isUnique;
        }

        return $this->isUnique = $isUnique;
    }

    /**
     * Loads the results of a stat() call into appropriate variables.
     *
     * @return void
     */
    protected function loadStats()
    {
        if ($this->inode !== null) {
            return;
        }

        $stat = stat($this->getPath());

        $this->device = $stat['dev'];
        $this->inode = $stat['ino'];
        $this->size = $stat['size'];
    }
}
