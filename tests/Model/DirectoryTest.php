<?php

/**
 * This file is part of the funique test suite.
 *
 * (c) Doug Harple <dharple@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Outsanity\Tests\Funique\Model;

use Exception;
use Ramsey\Uuid\Uuid;
use Outsanity\Funique\Model\Directory;
use Outsanity\Funique\Model\File;
use Outsanity\Tests\Funique\BaseTest;
use Outsanity\Tests\Funique\Mock\Model\Directory as MockDirectory;
use Outsanity\Tests\Funique\Mock\Model\File as MockFile;

/**
 * Tests for the Directory model.
 */
class DirectoryTest extends BaseTest
{
    /**
     * Returns an array of File from a (temporary) physical directory
     *
     * @return File[]
     */
    protected function getCheckFiles(): array
    {
        $dir = new MockDirectory('/');

        $minimumSize = 256;
        $unit = 1024;

        $size1 = random_int($minimumSize + ($unit * 1), $minimumSize + ($unit * 2));
        $size2 = random_int($minimumSize + ($unit * 4), $minimumSize + ($unit * 8));

        $filler1 = Uuid::uuid4() . "\n";
        $filler2 = Uuid::uuid1() . "\n";

        //
        // Build
        //

        $files = [];
        $files[] = (new MockFile(sprintf('test.%s', Uuid::uuid4()), $dir))
            ->setSize($size1)
            ->setFiller($filler1);

        $files[] = (new MockFile(sprintf('test.%s', Uuid::uuid4()), $dir))
            ->setSize($size1)
            ->setFiller($filler1);

        $files[] = (new MockFile(sprintf('test.%s', Uuid::uuid4()), $dir))
            ->setSize($size2)
            ->setFiller($filler1);

        $files[] = (new MockFile(sprintf('test.%s', Uuid::uuid4()), $dir))
            ->setSize($size1)
            ->setFiller($filler2);

        $tempDirName = $this->buildTestDirectory($files);

        //
        // Load
        //

        $tempDir = new Directory($tempDirName);

        $checkFiles = [];
        foreach ($files as $name => $file) {
            $checkFiles[$name] = new File($file->getFilename(), $tempDir);
        }

        return $checkFiles;
    }

    /**
     * Tests the getEntries() method
     *
     * @return void
     */
    public function testGetEntries()
    {
        $checkFiles = $this->getCheckFiles();
        $dir = $checkFiles[array_key_first($checkFiles)]->getDirectory();
        $entries = $dir->getEntries();

        $this->assertEquals($entries, $checkFiles);
    }
}
