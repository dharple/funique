<?php

namespace Funique\Model;

class File
{

	/**
	 *
	 * @var Directory
	 */
	protected $dir;

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
	protected $sizeThreshold = 16*1024*1024; // anything bigger than 16 megs runs md5 from the shell

	/**
	 *
	 */
	protected $sleepTime = 100000;

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
	public function getPath()
	{
		return $this->dir->getPath() . '/' . $this->file;
	}

	/**
	 *
	 */
	public function getSize()
	{
		if ($this->size !== null) {
			return $this->size;
		}

		return $this->size = filesize($this->getPath());
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

		// sleep briefly; that was expensive
		usleep($this->sleepTime);

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
}

