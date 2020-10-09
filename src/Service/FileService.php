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
 *
 * @todo switch from debugging to screen to logging?
 */
class FileService
{
    protected const LARGE_FILE = 65536;

    /**
     * Determines whether or not two files are the same.
     *
     * @param File          $fileLeft  The left hand file.
     * @param File          $fileRight The right hand file.
     * @param ?SymfonyStyle $io        A CLI styling interface.
     *
     * @return bool
     */
    public function sameFile(File $fileLeft, File $fileRight, ?SymfonyStyle $io = null): bool
    {
        if ($fileLeft->getSize() != $fileRight->getSize()) {
            return false;
        }

        if (isset($io)) {
            $io->text(sprintf('comparing left: %s', $fileLeft));
            $io->text(sprintf('against right:  %s', $fileRight));
            $io->text('size matches');
        }

        if ($fileLeft->getDevice() == $fileRight->getDevice() && $fileLeft->getInode() == $fileRight->getInode()) {
            if (isset($io)) {
                $io->text('device and inode match');
            }

            return true;
        }

        if ($fileLeft->getSize() > static::LARGE_FILE) {
            if ($fileLeft->getLeadingSum() != $fileRight->getLeadingSum()) {
                if (isset($io)) {
                    $io->text('leading checksum does not match');
                }
                return false;
            }

            if (isset($io)) {
                $io->text('leading checksum matches');
            }
        }

        if ($fileLeft->getSum() == $fileRight->getSum()) {
            if (isset($io)) {
                $io->text('checksum matches');
            }

            return true;
        }

        return false;
    }
}
