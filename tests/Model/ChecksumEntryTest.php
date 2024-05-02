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
use Outsanity\Funique\Model\ChecksumEntry;
use Outsanity\Tests\Funique\BaseTestCase;

/**
 * Tests for the ChecksumEntry model.
 */
class ChecksumEntryTest extends BaseTestCase
{
    protected const HASH_ALGORITHM = 'whirlpool';

    /**
     * Tests the getPath() method
     *
     * @return void
     */
    public function testGetPath()
    {
        $sum = hash(self::HASH_ALGORITHM, Uuid::uuid1());
        $path = sprintf('%s/%s', sys_get_temp_dir(), Uuid::uuid4());
        $expected = sprintf('CHECKSUM: %s', $path);

        $checksumEntry = new ChecksumEntry($sum, $path);

        $this->assertEquals($expected, $checksumEntry->getPath());
    }

    /**
     * Tests the getRelativePath() method
     *
     * @return void
     */
    public function testGetRelativePath()
    {
        $sum = hash(self::HASH_ALGORITHM, Uuid::uuid1());
        $path = sprintf('%s/%s', sys_get_temp_dir(), Uuid::uuid4());
        $expected = sprintf('CHECKSUM: %s', $path);

        $checksumEntry = new ChecksumEntry($sum, $path);

        $this->assertEquals($expected, $checksumEntry->getRelativePath());
    }

    /**
     * Tests the getSum() method
     *
     * @return void
     */
    public function testGetSum()
    {
        $sum = hash(self::HASH_ALGORITHM, Uuid::uuid1());
        $path = sprintf('%s/%s', sys_get_temp_dir(), Uuid::uuid4());

        $checksumEntry = new ChecksumEntry($sum, $path);

        $this->assertEquals($sum, $checksumEntry->getSum(self::HASH_ALGORITHM));
    }
}
