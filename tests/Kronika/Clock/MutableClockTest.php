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
use Kronika\Clock\MutableClock;
use Kronika\Clock\SystemClock;
use Kronika\Duration;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Depends;
use PHPUnit\Framework\Attributes\DependsOnClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(MutableClock::class)]
final class MutableClockTest extends TestCase
{
    private MutableClock $clock;
    private \DateTimeZone $timezone;

    #[\Override]
    protected function setUp(): void
    {
        $this->timezone = new \DateTimeZone('+01:00');
        $this->clock = new MutableClock(new SystemClock($this->timezone));
    }

    #[DependsOnClass(SystemClockTest::class)]
    public function testNow(): void
    {
        $time = $this->clock->now();
        \usleep(2);

        self::assertEquals($this->timezone, $this->clock->now()->timezone());
        self::assertNotSame($time, $this->clock->now());
    }

    #[Depends('testNow')]
    public function testFreeze(): void
    {
        $before = $this->clock->now();

        \usleep(2);
        $this->clock->freeze();
        $after = $this->clock->now();
        \usleep(2);

        self::assertEquals($this->timezone, $after->timezone());
        self::assertNotEquals($before, $after);
        self::assertSame($after, $this->clock->now());
    }

    #[Depends('testFreeze')]
    public function testFreezeWithTime(): void
    {
        $time = $this->clock->now()->add(Duration::of(days: 5));

        $this->clock->freeze($time);

        self::assertSame($time, $this->clock->now());
    }

    #[Depends('testNow')]
    public function testFreezeWhenAlreadyFrozen(): void
    {
        $this->clock->freeze();
        $before = $this->clock->now();

        \usleep(2);
        $this->clock->freeze();
        $after = $this->clock->now();

        self::assertEquals($this->timezone, $after->timezone());
        self::assertSame($before, $after);
    }

    #[Depends('testNow')]
    public function testChangeClock(): void
    {
        $before = $this->clock->now();
        $time = new \DateTimeImmutable('2025-12-31 12:15:30.999999 +01:00');

        \usleep(2);
        $this->clock->changeClock(new FrozenClock($time));
        $after = $this->clock->now();
        \usleep(2);

        self::assertNotEquals($before, $after);
        self::assertEquals($time, $after);
        self::assertSame($after, $this->clock->now());
    }

    #[Depends('testFreeze')]
    public function testReset(): void
    {
        $before = new \DateTimeImmutable('2025-12-31 12:15:30.999999 +01:00');
        $this->clock->freeze($before);

        \usleep(2);
        $this->clock->reset();
        $after = $this->clock->now();

        self::assertNotEquals($before, $after);
        self::assertNotSame($after, $this->clock->now());
    }
}
