<?php

/**
 * This file is part of the funique package.
 *
 * (c) Doug Harple <dharple@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Funique\Service;

use Funique\Model\Directory;
use Funique\Model\File;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * Provides service methods for File objects
 */
class FileService
{
    /**
     * Determines whether or not two files are the same.
     *
     * @param File         $fileLeft  The left hand file.
     * @param File         $fileRight The right hand file.
     * @param SymfonyStyle $debugIo   A CLI styling interface.
     *
     * @return bool
     */
    public function sameFile(File $fileLeft, File $fileRight, SymfonyStyle $debugIo): bool
    {
        if ($fileLeft->isHardlinkOf($fileRight)) {
            $debugIo->text(sprintf('comparing left: %s', $fileLeft));
            $debugIo->text(sprintf('against right:  %s', $fileRight));
            $debugIo->text('device and inode match');
            return true;
        }

        if ($fileLeft->isSameAs($fileRight)) {
            $debugIo->text(sprintf('comparing left: %s', $fileLeft));
            $debugIo->text(sprintf('against right:  %s', $fileRight));
            $debugIo->text('size and checksum match');
            return true;
        }

        return false;
    }
}
