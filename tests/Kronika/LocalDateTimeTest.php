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

namespace Kronika\Tests;

use Kronika\Date;
use Kronika\LocalDateTime;
use Kronika\Time;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class LocalDateTimeTest extends TestCase
{
    public static function ofProvider(): array
    {
        return [
            [Date::of(2025, 3, 24), Time::noon()],
        ];
    }

    #[DataProvider('ofProvider')]
    public function testBasic(Date $date, Time $time): void
    {
        $datetime = LocalDateTime::of(date: $date, time: $time);

        $this->assertEquals($date, $datetime->date());
        $this->assertEquals($time, $datetime->time());
    }

    public function testToString(): void
    {
        $datetime = LocalDateTime::of(Date::of(2025, 3, 24), Time::endOfDay());

        $this->assertEquals('2025-03-24T23:59:59.999999', (string) $datetime);
    }
}
