<?php

/**
 * This file is part of the funique package.
 *
 * (c) Doug Harple <dharple@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Outsanity\Funique\Service;

/**
 * Provides an access service
 */
class AccessService
{
    /**
     * Last access, generated using hrtime().
     *
     * @var ?string
     */
    protected static ?string $lastAccess = null;

    /**
     * Returns the last access based on hrtime()
     *
     * @return ?string
     */
    public static function getLastAccess(): ?string
    {
        return static::$lastAccess;
    }

    /**
     * Sets the last access based on hrtime()
     */
    public static function setLastAccess(): void
    {
        static::$lastAccess = hrtime(true);
    }
}
