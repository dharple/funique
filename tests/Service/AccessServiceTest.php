<?php

/**
 * This file is part of the funique test suite.
 *
 * (c) Doug Harple <dharple@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Outsanity\Tests\Funique\Service;

use Exception;
use Outsanity\Funique\Service\AccessService;
use Outsanity\Tests\Funique\BaseTest;

/**
 * Tests for the AccessService model.
 */
class AccessServiceTest extends BaseTest
{
    /**
     * How many times to test setLastAccess.
     *
     * @var int
     */
    protected const CHECK_COUNT = 10;

    /**
     * How long to sleep when checking these methods.
     *
     * @var int
     */
    protected const SLEEP_TIME = 100;

    /**
     * Tests the getLastAccess() method
     *
     * @return void
     */
    public function testGetLastAccess()
    {
        $expected = AccessService::getLastAccess();

        usleep(static::SLEEP_TIME);

        $this->assertEquals($expected, AccessService::getLastAccess());
    }

    /**
     * Tests the setLastAccess() method
     *
     * @return void
     */
    public function testSetLastAccess()
    {
        $previous = AccessService::getLastAccess();

        for ($i = 0; $i < static::CHECK_COUNT; $i++) {
            usleep(static::SLEEP_TIME * $i);

            $this->assertEquals($previous, AccessService::getLastAccess());

            AccessService::setLastAccess();
            $change = AccessService::getLastAccess();

            $this->assertNotEquals($previous, AccessService::getLastAccess());
            $this->assertEquals($change, AccessService::getLastAccess());

            $previous = $change;
        }
    }
}
