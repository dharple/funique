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
 * Tests for the File model.
 */
class FileTest extends BaseTest
{
    protected const FILE_CORRECT1   = 'same1';
    protected const FILE_CORRECT2   = 'same2';
    protected const FILE_WRONG_SIZE = 'wrong-size';
    protected const FILE_WRONG_DATA = 'wrong-data';

    /**
     * Returns an array of File from a (temporary) physical directory
     *
     * @param bool $largeFiles Set to true to create files large enough to
     *                         trigger the leading checksum calculation.
     *
     * @return File[]
     */
    protected function getCheckFiles(bool $largeFiles = false): array
    {
        $dir = new MockDirectory('/');

        if ($largeFiles) {
            $minimumSize = (new MockFile('large', $dir))->getLeadingChecksumMinimumFilesize();
        } else {
            $minimumSize = (new MockFile('normal', $dir))->getLeadingChecksumSize();
        }

        $unit = 1024;

        $size1 = rand($minimumSize + ($unit * 1), $minimumSize + ($unit * 2));
        $size2 = rand($minimumSize + ($unit * 4), $minimumSize + ($unit * 8));

        $filler1 = Uuid::uuid4() . "\n";
        $filler2 = Uuid::uuid1() . "\n";

        //
        // Build
        //

        $files = [];
        $files[self::FILE_CORRECT1] = (new MockFile(sprintf('test.%s', Uuid::uuid4()), $dir))
            ->setSize($size1)
            ->setFiller($filler1);

        $files[self::FILE_CORRECT2] = (new MockFile(sprintf('test.%s', Uuid::uuid4()), $dir))
            ->setSize($size1)
            ->setFiller($filler1);

        $files[self::FILE_WRONG_SIZE] = (new MockFile(sprintf('test.%s', Uuid::uuid4()), $dir))
            ->setSize($size2)
            ->setFiller($filler1);

        $files[self::FILE_WRONG_DATA] = (new MockFile(sprintf('test.%s', Uuid::uuid4()), $dir))
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
     * Generates a temp filename based on method and algorithm.
     *
     * @param string  $path      The base path for this file.
     * @param string  $method    The method calling this function.
     * @param ?string $secondary The secondary name to use.
     *
     * @return string
     */
    public function getTempFilename(string $path, string $method, ?string $secondary = null)
    {
        if (str_contains($method, '::')) {
            $hold = explode('::', $method);
            $method = $hold[1];
        }

        $filename = sprintf(
            '%s.%s',
            $path,
            preg_replace('/[^a-z0-9]/i', '-', $method)
        );

        if ($secondary !== null) {
            $filename = sprintf(
                '%s.%s',
                $filename,
                preg_replace('/[^a-z0-9]/i', '-', $secondary)
            );
        }

        return $filename;
    }

    /**
     * Tests the getDevice() method
     *
     * @return void
     */
    public function testGetDevice()
    {
        $checkFiles = $this->getCheckFiles();

        $this->assertEquals($checkFiles[self::FILE_CORRECT1]->getDevice(), $checkFiles[self::FILE_CORRECT2]->getDevice());
        $this->assertEquals($checkFiles[self::FILE_CORRECT1]->getDevice(), $checkFiles[self::FILE_WRONG_SIZE]->getDevice());
        $this->assertEquals($checkFiles[self::FILE_CORRECT1]->getDevice(), $checkFiles[self::FILE_WRONG_DATA]->getDevice());
    }

    /**
     * Tests the getInode() method
     *
     * @return void
     */
    public function testGetInode()
    {
        $checkFiles = $this->getCheckFiles();

        $this->assertNotEquals($checkFiles[self::FILE_CORRECT1]->getInode(), $checkFiles[self::FILE_CORRECT2]->getInode());
        $this->assertNotEquals($checkFiles[self::FILE_CORRECT1]->getInode(), $checkFiles[self::FILE_WRONG_SIZE]->getInode());
        $this->assertNotEquals($checkFiles[self::FILE_CORRECT1]->getInode(), $checkFiles[self::FILE_WRONG_DATA]->getInode());

        foreach ($checkFiles as $file) {
            $path = $file->getPath();
            $newPath = $this->getTempFilename($path, __METHOD__);

            link($path, $newPath);

            $newFile = new File(basename($newPath), $file->getDirectory());
            $this->assertTrue($file->isHardlinkOf($newFile));
        }
    }

    /**
     * Tests the getLeadingSum() method
     *
     * @return void
     */
    public function testGetLeadingSum()
    {
        $checkFiles = $this->getCheckFiles();

        $this->assertEquals($checkFiles[self::FILE_CORRECT1]->getLeadingSum(), $checkFiles[self::FILE_CORRECT2]->getLeadingSum());
        $this->assertEquals($checkFiles[self::FILE_CORRECT1]->getLeadingSum(), $checkFiles[self::FILE_WRONG_SIZE]->getLeadingSum());
        $this->assertNotEquals($checkFiles[self::FILE_CORRECT1]->getLeadingSum(), $checkFiles[self::FILE_WRONG_DATA]->getLeadingSum());
    }

    /**
     * Tests the getSize() method
     *
     * @return void
     */
    public function testGetSize()
    {
        $checkFiles = $this->getCheckFiles();

        $this->assertEquals($checkFiles[self::FILE_CORRECT1]->getSize(), $checkFiles[self::FILE_CORRECT2]->getSize());
        $this->assertNotEquals($checkFiles[self::FILE_CORRECT1]->getSize(), $checkFiles[self::FILE_WRONG_SIZE]->getSize());
        $this->assertEquals($checkFiles[self::FILE_CORRECT1]->getSize(), $checkFiles[self::FILE_WRONG_DATA]->getSize());
    }

    /**
     * Tests the getSum() method
     *
     * @return void
     */
    public function testGetSum()
    {
        $checkFiles = $this->getCheckFiles();

        $algorithms = hash_algos();

        // pre-seed cached checksum
        foreach ($checkFiles as $file) {
            $checkFiles[self::FILE_CORRECT1]->getSum($algorithms[array_rand($algorithms)]);
        }

        foreach ($algorithms as $algorithm) {
            $this->assertEquals($checkFiles[self::FILE_CORRECT1]->getSum($algorithm), $checkFiles[self::FILE_CORRECT2]->getSum($algorithm));
            $this->assertNotEquals($checkFiles[self::FILE_CORRECT1]->getSum($algorithm), $checkFiles[self::FILE_WRONG_SIZE]->getSum($algorithm));
            $this->assertNotEquals($checkFiles[self::FILE_CORRECT1]->getSum($algorithm), $checkFiles[self::FILE_WRONG_DATA]->getSum($algorithm));
        }
    }

    /**
     * Confirms the getLeadingSum() method throws an Exception with an invalid file.
     *
     * @return void
     */
    public function testInvalidFileGetLeadingSum()
    {
        $this->expectException(Exception::class);

        $file = new File(Uuid::uuid4(), new Directory(sys_get_temp_dir()));

        $file->getLeadingSum();
    }

    /**
     * Confirms the getSize() method throws an Exception with an invalid file.
     *
     * @return void
     */
    public function testInvalidFileGetSum()
    {
        $this->expectException(Exception::class);

        $file = new File(Uuid::uuid4(), new Directory(sys_get_temp_dir()));

        $file->getSize();
    }

    /**
     * Tests the isHardlinkOf() method
     *
     * @return void
     */
    public function testIsHardlinkOf()
    {
        $checkFiles = $this->getCheckFiles();

        $this->assertFalse($checkFiles[self::FILE_CORRECT1]->isHardlinkOf($checkFiles[self::FILE_CORRECT2]));
        $this->assertFalse($checkFiles[self::FILE_CORRECT1]->isHardlinkOf($checkFiles[self::FILE_WRONG_SIZE]));
        $this->assertFalse($checkFiles[self::FILE_CORRECT1]->isHardlinkOf($checkFiles[self::FILE_WRONG_DATA]));

        $this->assertFalse($checkFiles[self::FILE_CORRECT2]->isHardlinkOf($checkFiles[self::FILE_WRONG_SIZE]));
        $this->assertFalse($checkFiles[self::FILE_CORRECT2]->isHardlinkOf($checkFiles[self::FILE_WRONG_DATA]));

        $this->assertFalse($checkFiles[self::FILE_WRONG_SIZE]->isHardlinkOf($checkFiles[self::FILE_WRONG_DATA]));

        foreach ($checkFiles as $file) {
            $path = $file->getPath();
            $newPath = $this->getTempFilename($path, __METHOD__);

            link($path, $newPath);

            $newFile = new File(basename($newPath), $file->getDirectory());

            $this->assertTrue($file->isHardlinkOf($newFile));
        }
    }

    /**
     * Tests the isSameAs() method
     *
     * @return void
     */
    public function testIsSameAs()
    {
        $checkFiles = $this->getCheckFiles(true);

        $algorithms = hash_algos();

        // pre-seed cached checksum
        foreach ($checkFiles as $file) {
            $checkFiles[self::FILE_CORRECT1]->getSum($algorithms[array_rand($algorithms)]);
        }

        foreach ($algorithms as $algorithm) {
            $this->assertTrue($checkFiles[self::FILE_CORRECT1]->isSameAs($checkFiles[self::FILE_CORRECT2], $algorithm));
            $this->assertFalse($checkFiles[self::FILE_CORRECT1]->isSameAs($checkFiles[self::FILE_WRONG_SIZE], $algorithm));
            $this->assertFalse($checkFiles[self::FILE_CORRECT1]->isSameAs($checkFiles[self::FILE_WRONG_DATA], $algorithm));

            $this->assertFalse($checkFiles[self::FILE_CORRECT2]->isSameAs($checkFiles[self::FILE_WRONG_SIZE], $algorithm));
            $this->assertFalse($checkFiles[self::FILE_CORRECT2]->isSameAs($checkFiles[self::FILE_WRONG_DATA], $algorithm));

            $this->assertFalse($checkFiles[self::FILE_WRONG_SIZE]->isSameAs($checkFiles[self::FILE_WRONG_DATA], $algorithm));

            foreach ($checkFiles as $file) {
                $path = $file->getPath();
                $newPath = $this->getTempFilename($path, __METHOD__, $algorithm);

                link($path, $newPath);

                $newFile = new File(basename($newPath), $file->getDirectory());
                $this->assertTrue($file->isSameAs($newFile, $algorithm));
            }
        }
    }

    /**
     * Tests the isUnique() method
     *
     * @return void
     */
    public function testIsUnique()
    {
        $file = new File(Uuid::uuid4(), new Directory(sys_get_temp_dir()));

        $file->isUnique(true);
        $this->assertTrue($file->isUnique());

        $file->isUnique(false);
        $this->assertFalse($file->isUnique());

        $file->isUnique(true);
        $this->assertTrue($file->isUnique());
    }
}
