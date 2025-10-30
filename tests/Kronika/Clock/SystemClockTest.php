<?php

declare(strict_types=1);

/**
 * This file is part of the Kronika package.
 *
 * (c) Ivan Kudinov <i@ikudinov.pro>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Kronika\Tests\Clock;

use Kronika\Clock\SystemClock;
use Kronika\Tests\ZonedDateTimeTest;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DependsOnClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(SystemClock::class)]
final class SystemClockTest extends TestCase
{
    #[DependsOnClass(ZonedDateTimeTest::class)]
    public function testClock(): void
    {
        $timezone = new \DateTimeZone('+01:00');
        $clock = new SystemClock($timezone);

        $time = $clock->now();
        \usleep(2);

        self::assertEquals($timezone, $time->timezone());
        self::assertNotEquals($time, $clock->now());
    }
}
