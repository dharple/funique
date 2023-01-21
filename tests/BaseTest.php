<?php

/**
 * This file is part of the funique test suite.
 *
 * (c) Doug Harple <dharple@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Outsanity\Tests\Funique;

use Outsanity\Tests\Funique\Mock\Model\File;
use PHPUnit\Framework\TestCase;
use Ramsey\Uuid\Uuid;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Filesystem\Path;

/**
 * Defines a test case in the funique test suite.
 */
abstract class BaseTest extends TestCase
{
    /**
     * Builds a test directory with files described in an array.
     *
     * @param File[] $files An array of mock Files to turn into real files.
     *
     * @return string The directory that was created
     *
     * @throws \Exception If you try to break out of jail.
     */
    protected function buildTestDirectory(array $files): string
    {
        $filesystem = new Filesystem();

        $tempDir = Path::canonicalize(sprintf('%s/funique-tests/%s', sys_get_temp_dir(), Uuid::uuid4()));

        foreach ($files as $file) {
            $name = Path::canonicalize($file->getPath());

            $fullPath = Path::canonicalize(sprintf('%s/%s', $tempDir, $name));

            if (substr($fullPath, 0, strlen($tempDir)) != $tempDir) {
                throw new \Exception(sprintf('Attempted to break out of jail using path %s', $file->getPath()));
            }

            try {
                $size = $file->getSize();
            } catch (\Exception $e) {
                $size = mt_rand(0, 1000000);
            }

            $repeatCount = (int) ceil($size / strlen($file->getFiller()));

            $filesystem->dumpFile($fullPath, substr(str_repeat($file->getFiller(), $repeatCount), 0, $size));
        }

        return $tempDir;
    }
}
