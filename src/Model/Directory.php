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

use Exception;

/**
 * Describes a directory
 */
class Directory extends Entry
{

    /**
     * The parent directory
     *
     * @var Directory
     */
    protected $parent;

    /**
     * Path relative to parent, if set.
     *
     * @var string
     */
    protected $path;

    /**
     * Constructs a new directory
     *
     * @param string     $path   The path of this directory, relative to the parent.
     * @param ?Directory $parent The parent directory.
     */
    public function __construct(string $path, ?Directory $parent = null)
    {
        $this->path = str_replace('~', getenv('HOME'), preg_replace('@/$@', '', $path));
        $this->parent = $parent;
    }

    /**
     * Returns an array of Entry records based on the contents of this Directory.
     *
     * Does not cache.
     *
     * @param bool $includeHidden Whether or not to include hidden files in the
     *                            entries.
     * @param bool $followLinks   Whether or not follow links.
     *
     * @return Entry[]
     *
     * @throws Exception
     */
    public function getEntries(bool $includeHidden = false, bool $followLinks = false)
    {
        $path = $this->getPath();

        $dp = dir($path);

        if ($dp === false) {
            throw new Exception('could not read directory: ' . $path);
        }

        $entries = [];

        while (($entry = $dp->read()) !== false) {
            if ($entry == '.' || $entry == '..') {
                continue;
            }

            // ignore .files for now
            if (!$includeHidden && preg_match('/^\./', $entry)) {
                continue;
            }

            if (!$followLinks && is_link($path . '/' . $entry)) {
                continue;
            }

            $fullPath = $path . '/' . $entry;

            if (is_dir($fullPath)) {
                $entries[] = new Directory($entry, $this);
            } else {
                $entries[] = new File($entry, $this);
            }
        }

        $dp->close();

        return $entries;
    }

    /**
     * Returns the full path of this directory.
     *
     * @return string
     */
    public function getPath(): string
    {
        return isset($this->parent) ? $this->parent->getPath() . '/' . $this->path : $this->path;
    }
}
