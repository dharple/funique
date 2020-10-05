<?php

namespace Funique\Model;

class Directory
    extends Entry
{

    /**
     *
     * @var Directory
     */
    protected $parent;

    /**
     * path relative to parent, if set
     *
     * @var string
     */
    protected $path;

    /**
     *
     */
    public function __construct($path, $parent = null)
    {
        $this->path = str_replace('~', getenv('HOME'), preg_replace('@/$@', '', $path));
        $this->parent = $parent;
    }

    /**
     * Does not cache
     *
     * @return Entry[]
     */
    public function getEntries($includeHidden = false, $followLinks = false)
    {
        $path = $this->getPath();

        $dp = dir($path);

        if ($dp === false) {
            throw new \Exception('could not read directory: ' . $path);
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
     *
     */
    public function getPath()
    {
        return $this->parent
            ? $this->parent->getPath() . '/' . $this->path
            : $this->path;
    }

}
