<?php

namespace Funique;

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

		return $this->sum = md5(file_get_contents($this->getPath()));
	}

	/**
	 *
	 */
	public function isUnique($isUnique = null) {
		if ($isUnique !== null) {
			return $this->isUnique;
		}

		return $this->isUnique = $isUnique;
	}
}

