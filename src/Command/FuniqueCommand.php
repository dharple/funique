<?php

/**
 * This file is part of the funique package.
 *
 * (c) Doug Harple <dharple@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Funique\Command;

use Funique\Model\Directory;
use Funique\Model\File;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputDefinition;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * Runs the full funique command.
 */
class FuniqueCommand extends Command
{

    /**
     * The number of bytes in each size group.
     *
     * @var int
     */
    protected $groupingDivisor = 256;

    /**
     * Sleep time in microseconds
     *
     * @var int
     */
    protected $sleepTime = 100000;

    /**
     * Configues funique.
     *
     * @return void
     */
    protected function configure()
    {
        $this
            ->setName('funique')
            ->setDefinition(
                new InputDefinition([
                    new InputOption(
                        'left',
                        null,
                        InputOption::VALUE_REQUIRED | InputOption::VALUE_IS_ARRAY,
                        'the left-hand directory or directories',
                        []
                    ),

                    new InputOption(
                        'right',
                        null,
                        InputOption::VALUE_REQUIRED | InputOption::VALUE_IS_ARRAY,
                        'the right-hand directory or directories',
                        []
                    ),

                    new InputOption(
                        'output',
                        'o',
                        InputOption::VALUE_REQUIRED,
                        'redirect output to file',
                        ''
                    ),
                ])
            );
    }

    /**
     * Runs the funique command.
     *
     * @param InputInterface  $input  The input interface.
     * @param OutputInterface $output The output interface.
     *
     * @return int
     *
     * @throws \Exception
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $io = new SymfonyStyle($input, $output);

        $sides = ['left', 'right'];

        foreach ($sides as $side) {
            if (count($input->getOption($side)) == 0) {
                throw new \Exception('Please specify at least one ' . $side . '-hand directory to review');
            }
        }

        $outputFile = $input->getOption('output');

        $outputHandle = fopen($outputFile ?: 'php://stdout', 'w');
        if ($outputHandle == false) {
            throw new \Exception('Unable to open ' .
                ($outputFile ?: 'STDOUT') . ' for writing');
        }

        if ($output->isVerbose() && $outputFile) {
            $io->text('redirecting output to ' . $outputFile);
        }

        $files = [];
        $sizeGroups = [];
        $leftHandCount = 0;

        foreach ($sides as $side) {
            $files[$side] = [];

            foreach ($input->getOption($side) as $path) {
                if ($output->isVerbose()) {
                    $io->text('loading ' . $side . '-hand side directory: ' . $path);
                }

                $dir = new Directory($path);
                $dirFiles = $this->loadDirectory($dir, $input, $output, $io);
                foreach ($dirFiles as $sizeGroup => $contents) {
                    if (array_key_exists($sizeGroup, $files[$side])) {
                        $files[$side][$sizeGroup] = array_merge($files[$side][$sizeGroup], $contents);
                    } else {
                        $files[$side][$sizeGroup] = $contents;
                    }

                    if ($side == 'left') {
                        $leftHandCount += count($contents);
                    }
                }
            }

            $sizeGroups = array_merge($sizeGroups, array_keys($files[$side]));
        }

        $sizeGroups = array_unique($sizeGroups);
        sort($sizeGroups);

        if ($output->isVerbose()) {
            $io->text('comparing loaded files');
        }

        if ($output->isVerbose() && !$output->isDebug()) {
            $io->progressStart($leftHandCount);
        }

        foreach ($sizeGroups as $sizeGroup) {
            if (empty($files['left'][$sizeGroup]) || empty($files['right'][$sizeGroup])) {
                continue;
            }

            if ($output->isDebug()) {
                $io->text('reviewing files between ' .
                    (($sizeGroup - 1) * $this->groupingDivisor) . ' and ' .
                    ($sizeGroup * $this->groupingDivisor) . ' bytes ');
            }

            $filesLeft = array_key_exists($sizeGroup, $files['left']) ? $files['left'][$sizeGroup] : [];
            $filesRight = array_key_exists($sizeGroup, $files['right']) ? $files['right'][$sizeGroup] : [];

            foreach ($filesLeft as $fileLeft) {
                foreach ($filesRight as $fileRight) {
                    if ($fileLeft->isUnique() === false && $fileRight->isUnique() === false) {
                        continue;
                    }

                    if ($fileLeft->getSize() != $fileRight->getSize()) {
                        continue;
                    }

                    if ($output->isDebug()) {
                        $io->text('comparing left: ' . $fileLeft);
                        $io->text('against right:  ' . $fileRight);
                        $io->text('size matches');
                    }

                    if (
                        $fileLeft->getDevice() == $fileRight->getDevice() &&
                        $fileLeft->getInode() == $fileRight->getInode()
                    ) {
                        if ($output->isDebug()) {
                            $io->text('device and inode match');
                        }

                        $fileLeft->isUnique(false);
                        $fileRight->isUnique(false);

                        continue;
                    }

                    if ($fileLeft->getSize() > 65536) {
                        if ($fileLeft->getLeadingSum() != $fileRight->getLeadingSum()) {
                            if ($output->isDebug()) {
                                $io->text('leading checksum does not match');
                            }
                            continue;
                        }
                        if ($output->isDebug()) {
                            $io->text('leading checksum matches');
                        }
                    }

                    if ($fileLeft->getSum() == $fileRight->getSum()) {
                        if ($output->isDebug()) {
                            $io->text('checksum matches');
                        }

                        $fileLeft->isUnique(false);
                        $fileRight->isUnique(false);
                    }
                }

                if ($output->isVerbose() && !$output->isDebug()) {
                    $io->progressAdvance();
                }

                usleep($this->sleepTime);
            }
        }

        if ($output->isVerbose() && !$output->isDebug()) {
            $io->progressFinish();
        }

        foreach ($sizeGroups as $sizeGroup) {
            $filesLeft = array_key_exists($sizeGroup, $files['left']) ? $files['left'][$sizeGroup] : [];
            $filesRight = array_key_exists($sizeGroup, $files['right']) ? $files['right'][$sizeGroup] : [];

            foreach ($filesLeft as $fileLeft) {
                if ($fileLeft->isUnique()) {
                    fprintf($outputHandle, $fileLeft . "\n");
                }
            }

            foreach ($filesRight as $fileRight) {
                if ($fileRight->isUnique()) {
                    fprintf($outputHandle, $fileRight . "\n");
                }
            }
        }

        fclose($outputHandle);

        return 0;
    }

    /**
     * Loads the contents of a directory.
     *
     * @param Directory       $dir    The directory to load.
     * @param InputInterface  $input  The input interface.
     * @param OutputInterface $output The output interface.
     * @param SymfonyStyle    $io     A CLI styling interface.
     *
     * @return File[][]
     */
    protected function loadDirectory(Directory $dir, InputInterface $input, OutputInterface $output, SymfonyStyle $io)
    {
        $ret = [];

        if ($output->isDebug()) {
            $io->text('loading dir: ' . $dir);
        }

        try {
            $entries = $dir->getEntries();
        } catch (\Exception $e) {
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

            $sizeGroup = (string) floor($size / $this->groupingDivisor);

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

            $merge = $this->loadDirectory($entry, $input, $output, $io);
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
