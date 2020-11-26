<?php

namespace Outsanity\Tests\Funique\Model;

use Exception;
use Outsanity\Funique\Model\Directory;
use Outsanity\Funique\Model\File;
use PHPUnit\Framework\TestCase;

class FileTest extends TestCase
{

    /**
     *
     */
    protected function getRandomString()
    {
        return sprintf('test-%d-%s', rand(), str_shuffle('dragonprince'));
    }

    /**
     * Tests the getSize() method with an invalid file.
     */
    public function testInvalidFile()
    {
        $this->expectException(Exception::class);

        $dir = sys_get_temp_dir();

        $file = new File($this->getRandomString(), new Directory($dir));

        $file->getSize();
    }
}
