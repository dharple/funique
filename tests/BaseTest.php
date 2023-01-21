<?php

namespace Outsanity\Tests\Funique;

use PHPUnit\Framework\TestCase;
use Ramsey\Uuid\Uuid;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Filesystem\Path;

abstract class BaseTest extends TestCase
{
    /**
     * Builds a test directory with files described in an array.
     *
     * @param File[] $files An array of files to create.  (Use a MockFile)
     *
     * @return string The directory that was created
     */
    protected function buildTestDirectory($files): string
    {
        $filesystem = new Filesystem();

        $tempDir = Path::canonicalize(sprintf("%s/funique-tests/%s", sys_get_temp_dir(), Uuid::uuid4()));

        foreach ($files as $file) {
            $name = Path::canonicalize($file->getPath());

            $fullPath = Path::canonicalize(sprintf("%s/%s", $tempDir, $name));

            if (substr($fullPath, 0, strlen($tempDir)) != $tempDir) {
                throw new \Exception(sprintf('Attempted to break out of jail using path %s', $file->getPath()));
            }

            try {
                $size = $file->getSize();
            } catch (\Exception $e) {
                $size = mt_rand(0, 1000000);
            }

            $filesystem->dumpFile($fullPath, substr(str_repeat($file->getFiller(), ceil($size / strlen($file->getFiller()))), 0, $size));
        }

        return $tempDir;
    }
}
