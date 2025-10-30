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

use Kronika\Clock\FrozenClock;
use Kronika\Clock\InaccurateClock;
use Kronika\Precision;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DependsOnClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(InaccurateClock::class)]
final class InaccurateClockTest extends TestCase
{
    #[DependsOnClass(FrozenClockTest::class)]
    public function testSecond(): void
    {
        $time = new \DateTimeImmutable('2025-12-31 12:15:30.000999 +01:00');
        $frozen = new FrozenClock($time);
        $clock = new InaccurateClock($frozen, Precision::Second);

        \usleep(2);

        self::assertEquals($frozen->now()->resetMicro(), $clock->now());
    }

    #[DependsOnClass(FrozenClockTest::class)]
    public function testMinute(): void
    {
        $time = new \DateTimeImmutable('2025-12-31 12:15:45.999999 +01:00');
        $frozen = new FrozenClock($time);
        $clock = new InaccurateClock($frozen, Precision::Minute);

        \usleep(2);

        self::assertEquals($frozen->now()->resetSecond(), $clock->now());
    }
}
