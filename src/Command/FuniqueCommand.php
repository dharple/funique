<?php
/**
 *
 */

namespace Funique\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputDefinition;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 *
 */
class FuniqueCommand extends Command
{

	/**
	 * Configues funique
	 */
	protected function configure()
	{
		$this
			->setName('funique')
			->setDefinition(
				new InputDefinition(array(
					new InputOption('left', null, 
						InputOption::VALUE_REQUIRED | InputOption::VALUE_IS_ARRAY,
						'the left-hand directory or directories', []),

					new InputOption('right', null,
						InputOption::VALUE_REQUIRED | InputOption::VALUE_IS_ARRAY,
						'the right-hand directory or directories', []),
				))
			);
	}

	/**
	 * Runs detox
	 */
	protected function execute(InputInterface $input, OutputInterface $output)
	{
		$io = new SymfonyStyle($input, $output);

		$sides = ['left', 'right'];

		$files = [];
		$sizeGroups = [];
		$leftHandCount = 0;

		foreach ($sides as $side) {
			$files[$side] = [];

			foreach ($input->getOption($side) as $path) {
				if ($output->isVerbose()) {
					$io->text('loading ' . $side . '-hand side directory: ' . $path);
				}

				$dir = new \Funique\Model\Directory($path, $input, $output);
				$dirFiles = $dir->getAllFiles();
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

		$sizeGroups = array_unique($sizeGroups, SORT_NUMERIC);

		if ($output->isVerbose()) {
			$io->text('comparing loaded files');
		}

		if ($output->isVerbose() && !$output->isDebug()) {
			$io->progressStart($leftHandCount);
		}

		foreach ($sizeGroups as $sizeGroup)
		{
			if ($output->isDebug()) {
				$io->note('reviewing size group: ' . $sizeGroup);
			}

			$filesLeft = array_key_exists($sizeGroup, $files['left']) ? $files['left'][$sizeGroup] : [];
			$filesRight = array_key_exists($sizeGroup, $files['right']) ? $files['right'][$sizeGroup] : [];

			foreach ($filesLeft as $fileLeft) {
				foreach ($filesRight as $fileRight) {
					if ($fileLeft->isUnique() === false && $fileRight->isUnique() === false) {
						continue;
					}

					if ($fileLeft->getSize() == $fileRight->getSize()) {
						if ($fileLeft->getSum() == $fileRight->getSum()) {
							$fileLeft->isUnique(false);
							$fileRight->isUnique(false);
						}
					}
				}

				if ($output->isVerbose() && !$output->isDebug()) {
					$io->progressAdvance();
				}
			}

		}

		if ($output->isVerbose() && !$output->isDebug()) {
			$io->progressFinish();
		}

		foreach ($sizeGroups as $sizeGroup)
		{
			$filesLeft = array_key_exists($sizeGroup, $files['left']) ? $files['left'][$sizeGroup] : [];
			$filesRight = array_key_exists($sizeGroup, $files['right']) ? $files['right'][$sizeGroup] : [];

			foreach ($filesLeft as $fileLeft) {
				if ($fileLeft->isUnique()) {
					$io->text($fileLeft->getPath());
				}
			}

			foreach ($filesRight as $fileRight) {
				if ($fileRight->isUnique()) {
					$io->text($fileRight->getPath());
				}
			}
		}

	}

}

