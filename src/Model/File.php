<?php

namespace Funique\Model;

class File
	extends Entry
{

	/**
	 * cached device
	 *
	 * @var int
	 */
	protected $device = null;

	/**
	 *
	 * @var Directory
	 */
	protected $dir;

	/**
	 * cached inode
	 *
	 * @var int
	 */
	protected $inode = null;

	/**
	 * filename
	 *
	 * @var string
	 */
	protected $file;

	/**
	 *
	 */
	protected $isUnique = true;

	/**
	 * cached size
	 *
	 * @var int
	 */
	protected $size = null;

	/**
	 *
	 * @var int
	 */
	protected $sizeThreshold = 1024*1024; // anything bigger than 1 meg runs md5 from the shell

	/**
	 * cached checksum
	 *
	 * @var string
	 */
	protected $sum = null;

	public function __construct($file, $dir)
	{
		$this->file = $file;
		$this->dir = $dir;
	}

	/**
	 *
	 */
	public function getDevice()
	{
		if ($this->device === null) {
			$this->loadStats();
		}

		return $this->device;
	}

	/**
	 *
	 */
	public function getInode()
	{
		if ($this->inode === null) {
			$this->loadStats();
		}

		return $this->inode;
	}

	/**
	 *
	 */
	public function getPath()
	{
		return $this->dir->getPath() . '/' . $this->file;
	}

	/**
	 *
	 */
	public function getSize()
	{
		if ($this->size === null) {
			$this->loadStats();
		}

		return $this->size;
	}

	/**
	 *
	 */
	public function getSum()
	{
		if ($this->sum !== null) {
			return $this->sum;
		}

		if ($this->getSize() <= $this->sizeThreshold) {
			$this->sum = md5(file_get_contents($this->getPath()));
		} else {
			$this->sum = exec('md5sum ' . escapeshellarg($this->getPath()) . ' | cut -f 1 -d " "');
		}

		return $this->sum;
	}

	/**
	 *
	 */
	public function isUnique($isUnique = null) {
		if ($isUnique === null) {
			return $this->isUnique;
		}

		return $this->isUnique = $isUnique;
	}

	/**
	 *
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

