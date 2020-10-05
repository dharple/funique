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
 * Describes either a directory or a file
 */
abstract class Entry
{
    /**
     * Returns the full path to this entry.
     *
     * @return string
     */
    public function __toString(): string
    {
        return $this->getPath();
    }

    /**
     * Returns the full path to this entry.
     *
     * @return string
     */
    abstract public function getPath(): string;
}
