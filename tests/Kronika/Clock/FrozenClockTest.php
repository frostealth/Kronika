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
use Kronika\Duration;
use Kronika\Tests\DurationTest;
use Kronika\Tests\ZonedDateTimeTest;
use Kronika\ZonedDateTime;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Depends;
use PHPUnit\Framework\Attributes\DependsOnClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(FrozenClock::class)]
final class FrozenClockTest extends TestCase
{
    private ZonedDateTime $time;
    private FrozenClock $clock;

    #[\Override]
    protected function setUp(): void
    {
        $this->time = ZonedDateTime::ofDateTime(new \DateTimeImmutable('2025-12-31 12:15:30.999999 +01:00'));
        $this->clock = new FrozenClock($this->time);
    }

    #[DependsOnClass(ZonedDateTimeTest::class)]
    public function testNow(): void
    {
        self::assertSame($this->time, $this->clock->now());

        \usleep(2);

        self::assertSame($this->time, $this->clock->now());
    }

    #[DependsOnClass(DurationTest::class)]
    #[Depends('testNow')]
    public function testSleep(): void
    {
        $before = $this->clock->now();
        $duration = Duration::of(seconds: 5, micros: 1);

        $this->clock->sleep($duration);

        self::assertNotEquals($before, $this->clock->now());
        self::assertSame($this->time->add($duration), $this->clock->now());
    }
}
