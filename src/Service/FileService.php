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

use Outsanity\Funique\Model\File;
use Outsanity\Funique\Model\Summable;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * Provides service methods for File objects
 */
class FileService
{
    /**
     * Determines whether or not two files are the same.
     *
     * @param Summable     $fileLeft          The left hand file.
     * @param Summable     $fileRight         The right hand file.
     * @param string       $checksumAlgorithm The checksum algorithm to use.
     * @param SymfonyStyle $debugIo           A CLI styling interface.
     *
     * @return bool
     */
    public function checkContents(Summable $fileLeft, Summable $fileRight, string $checksumAlgorithm, SymfonyStyle $debugIo): bool
    {
        if ($fileLeft->isSameAs($fileRight, $checksumAlgorithm)) {
            $debugIo->text(sprintf('comparing left: %s', $fileLeft));
            $debugIo->text(sprintf(' against right: %s', $fileRight));
            if (($fileLeft instanceof File) and ($fileRight instanceof File)) {
                $debugIo->text('size and checksums match');
            } else {
                $debugIo->text('checksums match');
            }
            return true;
        }

        return false;
    }

    /**
     * Determines whether or not two files are hardlinks of each other.
     *
     * @param Summable     $fileLeft  The left hand file.
     * @param Summable     $fileRight The right hand file.
     * @param SymfonyStyle $debugIo   A CLI styling interface.
     *
     * @return bool
     */
    public function checkHardlink(Summable $fileLeft, Summable $fileRight, SymfonyStyle $debugIo): bool
    {
        if (($fileLeft instanceof File) and ($fileRight instanceof File)) {
            if ($fileLeft->isHardlinkOf($fileRight)) {
                $debugIo->text(sprintf('comparing left: %s', $fileLeft));
                $debugIo->text(sprintf(' against right: %s', $fileRight));
                $debugIo->text('device and inode match');
                return true;
            }
        }

        return false;
    }
}
