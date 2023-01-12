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

use Exception;

/**
 * Describes a file
 */
abstract class Summable extends Entry
{
    /**
     * Whether or not this file is unique.
     *
     * @var boolean
     */
    protected $isUnique = true;

    /**
     * Returns the checksum for the entire file.
     *
     * @return string
     */
    abstract public function getSum(string $checksumAlgorithm): string;

    /**
     * Determines whether or not this file has the same contents as another
     * file.
     *
     * @param Summable $other             The other file to review.
     * @param string   $checksumAlgorithm The checksum algorithm to use.
     *
     * @return bool
     *
     * @throws Exception
     */
    public function isSameAs(Summable $other, string $checksumAlgorithm): bool
    {
        if (($this instanceOf File) && ($other instanceOf File)) {
            if ($this->getSize() != $other->getSize()) {
                return false;
            }

            if ($this->getSize() > static::LEADING_CHECKSUM_MINIMUM_FILESIZE) {
                if ($this->getLeadingSum() != $other->getLeadingSum()) {
                    return false;
                }
            }
        }

        return ($this->getSum($checksumAlgorithm) == $other->getSum($checksumAlgorithm));
    }

    /**
     * Whether or not the file is unique.  Defaults to true.
     *
     * @param ?bool $isUnique Pass to set, leave off to return.
     *
     * @return bool
     */
    public function isUnique(?bool $isUnique = null): bool
    {
        if ($isUnique === null) {
            return $this->isUnique;
        }

        return $this->isUnique = $isUnique;
    }
}
