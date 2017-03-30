<?php

namespace Funique;

class Directory
{

	/**
	 *
	 */
	protected $groupingDivisor = 256;

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
	protected $sleepTime = 100000; // ms

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

		$entries = [];

		while (($entry = $dp->read()) !== false) {
			if ($entry == '.' || $entry == '..') {
				continue;
			}

			// ignore .files for now
			if (preg_match('/^\./', $entry)) {
				continue;
			}

			if (is_link($path . '/' . $entry)) {
				continue;
			}

			$entries[] = $entry;
		}

		$dp->close();

		usleep($this->sleepTime);

		foreach ($entries as $entry) {
			$fullPath = $path . '/' . $entry;

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
			$sizeGroup = (string) floor($size / $this->groupingDivisor);

			if (!array_key_exists($sizeGroup, $ret)) {
				$ret[$sizeGroup] = [];
			}

			$ret[$sizeGroup][] = $file;
		}

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
