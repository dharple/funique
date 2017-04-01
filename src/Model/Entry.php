<?php

namespace Funique\Model;

abstract class Entry
{

	/**
	 *
	 */
	abstract public function getPath();

	/**
	 *
	 */
	public function __toString()
	{
		return $this->getPath();
	}

}

