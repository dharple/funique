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

use Exception;
use Outsanity\Funique\Model\Directory;
use Outsanity\Funique\Model\File;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * Provides service methods for Directory objects
 */
class DirectoryService
{
    /**
     * Sleep time in microseconds
     *
     * @var int
     */
    protected $sleepTime = 1000;

    /**
     * Loads the contents of a directory.
     *
     * @param Directory    $dir             The directory to load.
     * @param int          $groupingDivisor The divisor to split up groups by.
     * @param SymfonyStyle $debugIo         A CLI styling interface.
     *
     * @return File[][]
     */
    public function loadDirectory(Directory $dir, int $groupingDivisor, SymfonyStyle $debugIo)
    {
        $ret = [];

        $debugIo->text(sprintf('loading dir: %s', $dir));

        try {
            $entries = $dir->getEntries();
        } catch (Exception $e) {
            return [];
        }

        usleep($this->sleepTime);

        // files first

        foreach ($entries as $entry) {
            if (!($entry instanceof File)) {
                continue;
            }

            $size = $entry->getSize();
            if ($size == 0) {
                // ignore empty files
                continue;
            }

            $sizeGroup = (string) floor($size / $groupingDivisor);

            if (!array_key_exists($sizeGroup, $ret)) {
                $ret[$sizeGroup] = [];
            }

            $ret[$sizeGroup][] = $entry;
        }

        // then directories

        foreach ($entries as $entry) {
            if (!($entry instanceof Directory)) {
                continue;
            }

            $merge = $this->loadDirectory($entry, $groupingDivisor, $debugIo);
            foreach ($merge as $sizeGroup => $files) {
                if (!array_key_exists($sizeGroup, $ret)) {
                    $ret[$sizeGroup] = $files;
                } else {
                    $ret[$sizeGroup] = array_merge($ret[$sizeGroup], $files);
                }
            }
        }

        ksort($ret);

        return $ret;
    }
}
