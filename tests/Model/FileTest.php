<?php

namespace Outsanity\Tests\Funique\Model;

use Exception;
use Ramsey\Uuid\Uuid;
use Outsanity\Funique\Model\Directory;
use Outsanity\Funique\Model\File;
use Outsanity\Tests\Funique\BaseTest;
use Outsanity\Tests\Funique\Mock\Model\Directory as MockDirectory;
use Outsanity\Tests\Funique\Mock\Model\File as MockFile;

class FileTest extends BaseTest
{

    const FILE_CORRECT1   = 'same1';
    const FILE_CORRECT2   = 'same2';
    const FILE_WRONG_SIZE = 'wrong-size';
    const FILE_WRONG_DATA = 'wrong-data';

    /**
     * Returns an array of File from a (temporary) physical directory
     *
     * @return File[]
     *
     * @throws Exception
     */
    protected function getCheckFiles(): array
    {
        $dir = new MockDirectory('/');

        $minimumSize = (new MockFile('ignore', $dir))->getLeadingChecksumSize();

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
            $checkFiles[$name] = new File($file, $tempDir);
        }

        return $checkFiles;
    }

    /**
     * Confirms the getSize() method throws an Exception with an invalid file.
     */
    public function testInvalidFile()
    {
        $this->expectException(Exception::class);

        $file = new File(Uuid::uuid4(), new Directory(sys_get_temp_dir()));

        $file->getSize();
    }

    /**
     * Tests the getLeadingSum() method
     */
    public function testGetLeadingSum()
    {
        $checkFiles = $this->getCheckFiles();

        $this->assertEquals($checkFiles[self::FILE_CORRECT1]->getLeadingSum(), $checkFiles[self::FILE_CORRECT2]->getLeadingSum());
        $this->assertEquals($checkFiles[self::FILE_CORRECT1]->getLeadingSum(), $checkFiles[self::FILE_WRONG_SIZE]->getLeadingSum());
        $this->assertNotEquals($checkFiles[self::FILE_CORRECT1]->getLeadingSum(), $checkFiles[self::FILE_WRONG_DATA]->getLeadingSum());
    }

    /**
     * Tests the getSum() method
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
     */
    public function testGetSum()
    {
        $checkFiles = $this->getCheckFiles();

        $algorithms = ['sha512', 'sha256', 'sha1', 'md5'];
        foreach ($algorithms as $algorithm) {
            $this->assertEquals($checkFiles[self::FILE_CORRECT1]->getSum($algorithm), $checkFiles[self::FILE_CORRECT2]->getSum($algorithm));
            $this->assertNotEquals($checkFiles[self::FILE_CORRECT1]->getSum($algorithm), $checkFiles[self::FILE_WRONG_SIZE]->getSum($algorithm));
            $this->assertNotEquals($checkFiles[self::FILE_CORRECT1]->getSum($algorithm), $checkFiles[self::FILE_WRONG_DATA]->getSum($algorithm));
        }
    }

    /**
     * Tests the isHardlinkOf() method
     *
     * Ends up being coverage for getDevice() and getInode(), as well.
     */
    public function testIsHardlinkOf()
    {
        $checkFiles = $this->getCheckFiles();

        $this->assertFalse($checkFiles[self::FILE_CORRECT1]->isHardLinkOf($checkFiles[self::FILE_CORRECT2]));
        $this->assertFalse($checkFiles[self::FILE_CORRECT1]->isHardLinkOf($checkFiles[self::FILE_WRONG_SIZE]));
        $this->assertFalse($checkFiles[self::FILE_CORRECT1]->isHardLinkOf($checkFiles[self::FILE_WRONG_DATA]));

        $this->assertFalse($checkFiles[self::FILE_CORRECT2]->isHardLinkOf($checkFiles[self::FILE_WRONG_SIZE]));
        $this->assertFalse($checkFiles[self::FILE_CORRECT2]->isHardLinkOf($checkFiles[self::FILE_WRONG_DATA]));

        $this->assertFalse($checkFiles[self::FILE_WRONG_SIZE]->isHardLinkOf($checkFiles[self::FILE_WRONG_DATA]));

        foreach ($checkFiles as $file)
        {
            $path = $file->getPath();

            $newPath = sprintf('%s.%s', $path, __METHOD__);

            link($path, $newPath);

            $newFile = new File(basename($newPath), $file->getDirectory());

            $this->assertTrue($file->isHardlinkOf($newFile));
        }
    }
}
