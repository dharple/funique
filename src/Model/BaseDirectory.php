<?php

/**
 * This file is part of the funique package.
 *
 * (c) Doug Harple <dharple@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Outsanity\Funique\Model;

/**
 * Describes a directory
 */
class BaseDirectory extends Directory
{
    /**
     * Constructs a new directory
     *
     * @param string     $path   The path of this directory, relative to the parent.
     * @param ?Directory $parent The parent directory.
     */
    public function __construct(string $path, ?Directory $parent = null)
    {
        parent::__construct($path, $parent);

        $this->path = str_replace('~', getenv('HOME'), preg_replace('@/$@', '', $path));
    }
}
