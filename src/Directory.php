<?php

namespace Funique;

class Directory
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
		$this->path = preg_replace('@/$@', '', $path);
		$this->parent = $parent;
	}

	/**
	 * Does *not* cache.
	 *
	 * @return File[]
	 */
	public function getAllFiles()
	{
		$ret = [];

		$path = $this->getPath();

		$dp = dir($path);

		if ($dp === false) {
			fprintf(STDERR, "could not read: " . $path . "\n");
			return [];
		}

		while ($entry = $dp->read()) {
			if ($entry == '.' || $entry == '..') {
				continue;
			}

			// ignore .files for now
			if (preg_match('/^\./', $entry)) {
				continue;
			}

			$fullPath = $path . '/' . $entry;

			if (is_link($fullPath)) {
				continue;
			}

			if (is_dir($fullPath)) {
				$dir = new Directory($entry, $this);
				$merge = $dir->getAllFiles();
				foreach ($merge as $sizeGroup => $files) {
					if (!array_key_exists($sizeGroup, $ret)) {
						$ret[$sizeGroup] = $files;
					} else {
						$ret[$sizeGroup] = array_merge($ret[$sizeGroup], $files);
					}
				}
				continue;
			}

			$file = new File($entry, $this);
			$size = $file->getSize();
			$sizeGroup = (string) floor($size / 1024);

			if (!array_key_exists($sizeGroup, $ret)) {
				$ret[$sizeGroup] = [];
			}

			$ret[$sizeGroup][] = $file;
		}

		$dp->close();

		ksort($ret);

		return $ret;
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
