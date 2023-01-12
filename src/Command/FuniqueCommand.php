<?php

/**
 * This file is part of the funique package.
 *
 * (c) Doug Harple <dharple@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Outsanity\Funique\Command;

use Exception;
use Outsanity\Funique\Model\BaseDirectory;
use Outsanity\Funique\Model\ChecksumEntry;
use Outsanity\Funique\Model\Directory;
use Outsanity\Funique\Service\DirectoryService;
use Outsanity\Funique\Service\FileService;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Command\HelpCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Output\NullOutput;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * Runs the full funique command.
 */
class FuniqueCommand extends Command
{
    /**
     *
     */

    /**
     * The directory service.
     *
     * @var DirectoryService
     */
    protected $directoryService;

    /**
     * The file service.
     *
     * @var FileService
     */
    protected $fileService;

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
    protected $sleepTime = 1000;

    /**
     * Constructs a new funique command.
     */
    public function __construct()
    {
        parent::__construct();

        // Application isn't doing DI
        $this->directoryService = new DirectoryService();
        $this->fileService = new FileService();
    }

    /**
     * Configures funique.
     *
     * @return void
     */
    protected function configure()
    {
        $this
            ->setName('funique')
            ->setDescription('compares two sets of directories and/or checksum files, and reports files unique to one or the other')
            ->addOption('checksum', null, InputOption::VALUE_REQUIRED, 'the checksum algorithm to use', 'sha512')
            ->addOption('left-checksum-file', null, InputOption::VALUE_REQUIRED | InputOption::VALUE_IS_ARRAY, 'the left-hand checksum file(s)', [])
            ->addOption('left', 'l', InputOption::VALUE_REQUIRED | InputOption::VALUE_IS_ARRAY, 'the left-hand directory or directories', [])
            ->addOption('output', 'o', InputOption::VALUE_REQUIRED, 'redirect output to file', '')
            ->addOption('right-checksum-file', null, InputOption::VALUE_REQUIRED | InputOption::VALUE_IS_ARRAY, 'the right-hand checksum file(s)', [])
            ->addOption('right', 'r', InputOption::VALUE_REQUIRED | InputOption::VALUE_IS_ARRAY, 'the right-hand directory or directories', [])
        ;
    }

    /**
     * Runs the funique command.
     *
     * @param InputInterface  $input  The input interface.
     * @param OutputInterface $output The output interface.
     *
     * @return mixed
     *
     * @throws Exception
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $io = new SymfonyStyle($input, $output);
        $debugIo = $output->isDebug() ? $io : new SymfonyStyle($input, new NullOutput());

        $sides = ['left', 'right'];

        foreach ($sides as $side) {
            if (count($input->getOption($side)) == 0 && count($input->getOption(sprintf('%s-checksum-file', $side))) == 0) {
                $io->error(sprintf('please specify at least one %s-hand directory to review', $side));
                $help = new HelpCommand();
                $help->setCommand($this);
                return $help->run($input, $output);
            }
        }

        $outputFile = $input->getOption('output');

        $outputHandle = fopen($outputFile ?: 'php://stdout', 'w');
        if ($outputHandle == false) {
            $io->error(sprintf('unable to open %s for writing', $outputFile ?: 'STDOUT'));
            return Command::FAILURE;
        }

        if ($output->isVerbose() && $outputFile) {
            $io->text(sprintf('redirecting output to %s', $outputFile));
        }

        $checksumAlgorithm = $input->getOption('checksum');
        if (!in_array($checksumAlgorithm, hash_algos())) {
            $io->error(sprintf('unable to use algorithm %s for calculating checksums', $checksumAlgorithm));
            return Command::FAILURE;
        }

        $files = [];
        $sizeGroups = [];
        $leftHandCount = 0;

        $checksumFiles = [];

        foreach ($sides as $side) {
            $files[$side] = [];
            $checksumFiles[$side] = [];

            foreach ($input->getOption($side) as $path) {
                if ($output->isVerbose()) {
                    $io->text(sprintf('loading %s-hand side directory: %s', $side, $path));
                }

                $parent = null;
                if (!preg_match('@^/@', $path)) {
                    $parent = new BaseDirectory(getcwd());
                }

                $dir = new Directory($path, $parent);
                $dirFiles = $this->directoryService->loadDirectory($dir, $this->groupingDivisor, $debugIo);
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

            foreach ($input->getOption(sprintf('%s-checksum-file', $side)) as $checksumFile) {
                if ($output->isVerbose()) {
                    $io->text(sprintf('loading %s-hand side checksum file: %s', $side, $checksumFile));
                }

                foreach (file($checksumFile) as $line) {
                    list($checksum, $file) = explode(' ', $line, 2);

                    $checksumFiles[$side][] = new ChecksumEntry(trim($checksum), trim($file));

                    if ($side == 'left') {
                        $leftHandCount++;
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
            $filesLeft = array_key_exists($sizeGroup, $files['left']) ? $files['left'][$sizeGroup] : [];
            $filesRight = array_key_exists($sizeGroup, $files['right']) ? $files['right'][$sizeGroup] : [];

            if (count($checksumFiles['left']) > 0) {
                $filesLeft = array_merge($filesLeft, $checksumFiles['left']);
            }

            if (count($checksumFiles['right']) > 0) {
                $filesRight = array_merge($filesRight, $checksumFiles['right']);
            }

            if (empty($filesLeft) || empty($filesRight)) {
                continue;
            }

            $debugIo->text(sprintf(
                'reviewing files between %d and %d bytes',
                (($sizeGroup - 1) * $this->groupingDivisor),
                ($sizeGroup * $this->groupingDivisor)
            ));

            $iterationCount = 0;

            foreach ($filesLeft as $fileLeft) {
                foreach ($filesRight as $fileRight) {
                    if ($fileLeft->isUnique() === false && $fileRight->isUnique() === false) {
                        continue;
                    }

                    if ($this->fileService->sameFile($fileLeft, $fileRight, $checksumAlgorithm, $debugIo)) {
                        $fileLeft->isUnique(false);
                        $fileRight->isUnique(false);
                    }
                }

                if ($output->isVerbose() && !$output->isDebug()) {
                    $io->progressAdvance();
                }

                if (++$iterationCount % 10 == 0) {
                    usleep($this->sleepTime);
                }
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
                    fprintf($outputHandle, "%s\n", $fileLeft);
                }
            }

            foreach ($filesRight as $fileRight) {
                if ($fileRight->isUnique()) {
                    fprintf($outputHandle, "%s\n", $fileRight);
                }
            }
        }

        foreach ($checksumFiles as $side => $files) {
            foreach ($files as $file) {
                if ($file->isUnique()) {
                    fprintf($outputHandle, "%s\n", $file);
                }
            }
        }

        fclose($outputHandle);

        return Command::SUCCESS;
    }
}
