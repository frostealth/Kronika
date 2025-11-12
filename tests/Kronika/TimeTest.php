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
use Kronika\Duration;
use Kronika\Exception\FormatError;
use Kronika\Exception\InvalidTime;
use Kronika\Exception\MalformedString\TimeMalformedString;
use Kronika\LocalDateTime;
use Kronika\Precision;
use Kronika\Tests\Time\HourTest;
use Kronika\Tests\Time\MinuteTest;
use Kronika\Tests\Time\SecondTest;
use Kronika\Time;
use Kronika\ZonedDateTime;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Depends;
use PHPUnit\Framework\Attributes\DependsOnClass;
use PHPUnit\Framework\Attributes\TestWith;
use PHPUnit\Framework\TestCase;

#[CoversClass(Time::class)]
final class TimeTest extends TestCase
{
    private const int LESS = -1;
    private const int EQUAL = 0;
    private const int GREATER = 1;

    public static function ofProvider(): array
    {
        return [
            [Time\Hour::zero(), Time\Minute::zero(), Time\Second::zero()],
            [Time\Hour::of(5), Time\Minute::of(10), Time\Second::of(15)],
            [Time\Hour::of(9), Time\Minute::of(25), Time\Second::of(35, 9683)],
            [Time\Hour::of(12), Time\Minute::of(0), Time\Second::of(0)],
            [Time\Hour::of(15), Time\Minute::of(49), Time\Second::of(54, 429)],
            [Time\Hour::of(21), Time\Minute::of(57), Time\Second::of(42, 791472)],
            [Time\Hour::last(), Time\Minute::last(), Time\Second::last()],
        ];
    }

    #[DependsOnClass(HourTest::class)]
    #[DependsOnClass(MinuteTest::class)]
    #[DependsOnClass(SecondTest::class)]
    #[DataProvider('ofProvider')]
    public function testBasic(Time\Hour $hour, Time\Minute $minute, Time\Second $second): void
    {
        $time = Time::of(hour: $hour, minute: $minute, second: $second);

        self::assertEquals($hour, $time->hour());
        self::assertEquals($minute, $time->minute());
        self::assertEquals($second, $time->second());
        self::assertSame($time, Time::of(hour: $hour->value(), minute: $minute->value(), second: $second));
        self::assertEquals(
            $time->with($time->second()->resetMicro()),
            Time::of(hour: $hour->value(), minute: $minute->value(), second: $second->second()),
        );
    }

    #[TestWith([24, 00, 00])]
    #[TestWith([-1, 00, 00])]
    #[TestWith([23, 60, 00])]
    #[TestWith([23, -1, 00])]
    #[TestWith([23, 00, 60])]
    #[TestWith([23, 00, -1])]
    #[Depends('testBasic')]
    public function testInvalidValues(int $hour, int $minute, int $second): void
    {
        $this->expectException(InvalidTime::class);
        Time::of($hour, $minute, $second);
    }

    public static function withProvider(): array
    {
        return [
            [Time::midnight(), Time\Hour::last(), Time::of(hour: 23, minute: 0)],
            [Time::midday(), Time\Hour::zero(), Time::of(hour: 0, minute: 0)],
            [Time::endOfDay(), Time\Hour::of(20), Time::of(hour: 20, minute: 59, second: Time\Second::of(59, 999_999))],
            [Time::of(hour: 14, minute: 30, second: 45), Time\Hour::of(21), Time::of(hour: 21, minute: 30, second: 45)],
            [Time::of(hour: 14, minute: 30, second: 45), Time\Minute::of(21), Time::of(hour: 14, minute: 21, second: 45)],
            [Time::of(hour: 14, minute: 30, second: 45), Time\Second::of(18), Time::of(hour: 14, minute: 30, second: 18)],
        ];
    }

    #[Depends('testBasic')]
    #[DataProvider('withProvider')]
    public function testWith(Time $time, Time\TimeUnit $unit, Time $expected): void
    {
        $result = $time->with($unit);

        self::assertEquals($expected, $result);
    }

    #[TestWith(['H:i:s.u', '22:07:08.000001'])]
    #[TestWith(['H:i:s', '22:07:08'])]
    #[TestWith(['H i s', '22 07 08'])]
    #[TestWith(['i', '07'])]
    #[TestWith(['Y-m-d', 'Y-m-d'])]
    #[TestWith(['Y-m-d\TH:i:s.u', 'Y-m-dT22:07:08.000001'])]
    #[TestWith(['Y m d', 'Y m d'])]
    #[TestWith(['d/m/Y', 'd/m/Y'])]
    #[TestWith([
        '\T\i\m\e: "g \h\o\u\r\s, i \m\i\n\u\t\e\s, s \s\e\c\o\n\d\s"',
        'Time: "10 hours, 07 minutes, 08 seconds"',
    ])]
    #[Depends('testBasic')]
    public function testFormat(string $format, string $expected): void
    {
        $time = Time::of(22, 7, Time\Second::of(second: 8, micro: 1));

        self::assertEquals($expected, $time->format($format));
    }

    #[TestWith(['H:i:s.u', '22:07:08.000001'])]
    #[TestWith(['H:i:s', '22:07:08'])]
    #[TestWith(['H i s', '22 07 08'])]
    #[TestWith(['i', '07'])]
    #[TestWith(['Y-m-d', 'Y-m-d'])]
    #[TestWith(['Y-m-d\TH:i:s.u', 'Y-m-dT22:07:08.000001'])]
    #[TestWith(['Y m d', 'Y m d'])]
    #[TestWith(['d/m/Y', 'd/m/Y'])]
    #[TestWith([
        '\T\i\m\e: "g \h\o\u\r\s, i \m\i\n\u\t\e\s, s \s\e\c\o\n\d\s"',
        'Time: "10 hours, 07 minutes, 08 seconds"',
    ])]
    #[Depends('testFormat')]
    public function testOfFormat(string $format, string $str): void
    {
        $time = Time::ofFormat($format, $str);

        self::assertEquals($str, $time->format($format));
    }

    #[TestWith(['H:i:s.u', '22:07:08.0000001'])]
    #[TestWith(['H:i:s', '22:07'])]
    #[TestWith(['H i s', '07 08'])]
    #[TestWith(['H i s', ''])]
    #[TestWith(['', '22:12:08'])]
    #[TestWith(['', ''])]
    #[Depends('testOfFormat')]
    public function testOfFormatFail(string $format, string $str): void
    {
        $this->expectException(FormatError::class);
        Time::ofFormat($format, $str);
    }

    #[TestWith(['12:15:30.000999', [12, 15, 30, 999]])]
    #[TestWith(['12:15:30', [12, 15, 30]])]
    #[TestWith(['12:15:00.000999', [12, 15, 0, 999]])]
    #[TestWith(['12:15:00.12', [12, 15, 0, 120_000]])]
    #[TestWith(['12:00', [12, 0, 0]])]
    #[TestWith(['23:00', [23, 0, 0]])]
    #[TestWith(['00:00', [0, 0, 0]])]
    #[Depends('testBasic')]
    public function testParse(string $str, array $expected): void
    {
        $expected = Time::of($expected[0], $expected[1], Time\Second::of($expected[2], $expected[3] ?? 0));
        $actual = Time::parse($str);

        self::assertEquals($expected, $actual);
    }

    #[TestWith(['12'])]
    #[TestWith(['59'])]
    #[TestWith(['12 15'])]
    #[TestWith(['12 15 30.999'])]
    #[TestWith([''])]
    #[TestWith(['now'])]
    #[TestWith(['Now'])]
    #[TestWith(['today'])]
    #[TestWith(['Today'])]
    #[Depends('testParse')]
    public function testParseFail(string $str): void
    {
        $this->expectException(TimeMalformedString::class);
        Time::parse($str);
    }

    public static function ofDateTimeProvider(): array
    {
        return [
            [
                new \DateTime('1985-04-28 12:46:12.123456 UTC'),
                Time::of(hour: 12, minute: 46, second: Time\Second::of(second: 12, micro: 123456)),
            ],
            [new \DateTime('1985-05-21 02:06:02 +02:00'), Time::of(hour: 2, minute: 6, second: 2)],
            [new \DateTime('2020-07-01'), Time::midnight()],
            [
                new \DateTimeImmutable('1985-04-28 12:46:12.123456 UTC'),
                Time::of(hour: 12, minute: 46, second: Time\Second::of(second: 12, micro: 123456)),
            ],
            [new \DateTimeImmutable('1985-05-21 12:46:12 +02:00'), Time::of(hour: 12, minute: 46, second: 12)],
            [new \DateTimeImmutable('2020-07-01'), Time::midnight()],
            [new \DateTimeImmutable('23:59:59.999999'), Time::endOfDay()],
            [
                LocalDateTime::of(Date::of(1985, 4, 28), Time::of(12, 46, 12)),
                Time::of(hour: 12, minute: 46, second: 12),
            ],
            [
                ZonedDateTime::of(
                    Date::of(1985, 4, 28),
                    Time::of(12, 46, 12),
                    new \DateTimeZone('+10:00'),
                ),
                Time::of(hour: 12, minute: 46, second: 12),
            ],
            [Time::of(hour: 8, minute: 54, second: 39), Time::of(hour: 8, minute: 54, second: 39)],
        ];
    }

    #[Depends('testBasic')]
    #[DataProvider('ofDateTimeProvider')]
    public function ofDateTime(\DateTimeInterface|Time $input, Time $expected): void
    {
        $actual = Time::ofDateTime($input);

        self::assertEquals($expected, $actual);
    }

    #[Depends('testBasic')]
    public function testToString(): void
    {
        $time = Time::of(hour: 17, minute: 5, second: Time\Second::of(second: 39, micro: 4582));

        self::assertEquals('17:05:39.004582', (string)$time);
    }

    public static function comparisonProvider(): array
    {
        return [
            // Precision::Micro
            'Micro.Micro.Equal' => [
                Time::of(Time\Hour::of(12), Time\Minute::of(15), Time\Second::of(45, 4545)),
                Time::of(Time\Hour::of(12), Time\Minute::of(15), Time\Second::of(45, 4545)),
                Precision::Micro,
                self::EQUAL,
            ],
            'Micro.Micro.Less' => [
                Time::of(Time\Hour::of(12), Time\Minute::of(15), Time\Second::of(45, 4545)),
                Time::of(Time\Hour::of(12), Time\Minute::of(15), Time\Second::of(45, 5555)),
                Precision::Micro,
                self::LESS,
            ],
            'Micro.Micro.Greater' => [
                Time::of(Time\Hour::of(12), Time\Minute::of(15), Time\Second::of(45, 5555)),
                Time::of(Time\Hour::of(12), Time\Minute::of(15), Time\Second::of(45, 4545)),
                Precision::Micro,
                self::GREATER,
            ],
            'Micro.Second.Less' => [
                Time::of(Time\Hour::of(12), Time\Minute::of(15), Time\Second::of(45, 4545)),
                Time::of(Time\Hour::of(12), Time\Minute::of(15), Time\Second::of(50, 4545)),
                Precision::Micro,
                self::LESS,
            ],
            'Micro.Second.Greater' => [
                Time::of(Time\Hour::of(12), Time\Minute::of(15), Time\Second::of(50, 4545)),
                Time::of(Time\Hour::of(12), Time\Minute::of(15), Time\Second::of(45, 4545)),
                Precision::Micro,
                self::GREATER,
            ],
            'Micro.Minute.Less' => [
                Time::of(Time\Hour::of(12), Time\Minute::of(15), Time\Second::of(45, 4545)),
                Time::of(Time\Hour::of(12), Time\Minute::of(20), Time\Second::of(45, 4545)),
                Precision::Micro,
                self::LESS,
            ],
            'Micro.Minute.Greater' => [
                Time::of(Time\Hour::of(12), Time\Minute::of(20), Time\Second::of(45, 4545)),
                Time::of(Time\Hour::of(12), Time\Minute::of(15), Time\Second::of(45, 4545)),
                Precision::Micro,
                self::GREATER,
            ],
            'Micro.Hour.Less' => [
                Time::of(Time\Hour::of(12), Time\Minute::of(15), Time\Second::of(45, 4545)),
                Time::of(Time\Hour::of(20), Time\Minute::of(15), Time\Second::of(45, 4545)),
                Precision::Micro,
                self::LESS,
            ],
            'Micro.Hour.Greater' => [
                Time::of(Time\Hour::of(20), Time\Minute::of(15), Time\Second::of(45, 4545)),
                Time::of(Time\Hour::of(12), Time\Minute::of(15), Time\Second::of(45, 4545)),
                Precision::Micro,
                self::GREATER,
            ],

            // Precision::Second
            'Second.Micro.Less' => [
                Time::of(Time\Hour::of(12), Time\Minute::of(15), Time\Second::of(45, 4545)),
                Time::of(Time\Hour::of(12), Time\Minute::of(15), Time\Second::of(45, 5555)),
                Precision::Second,
                self::EQUAL,
            ],
            'Second.Micro.Greater' => [
                Time::of(Time\Hour::of(12), Time\Minute::of(15), Time\Second::of(45, 5555)),
                Time::of(Time\Hour::of(12), Time\Minute::of(15), Time\Second::of(45, 4545)),
                Precision::Second,
                self::EQUAL,
            ],
            'Second.Second.Less' => [
                Time::of(Time\Hour::of(12), Time\Minute::of(15), Time\Second::of(45, 4545)),
                Time::of(Time\Hour::of(12), Time\Minute::of(15), Time\Second::of(50, 4545)),
                Precision::Second,
                self::LESS,
            ],
            'Second.Second.Greater' => [
                Time::of(Time\Hour::of(12), Time\Minute::of(15), Time\Second::of(50, 4545)),
                Time::of(Time\Hour::of(12), Time\Minute::of(15), Time\Second::of(45, 4545)),
                Precision::Second,
                self::GREATER,
            ],
            'Second.Minute.Less' => [
                Time::of(Time\Hour::of(12), Time\Minute::of(15), Time\Second::of(45, 4545)),
                Time::of(Time\Hour::of(12), Time\Minute::of(20), Time\Second::of(45, 4545)),
                Precision::Second,
                self::LESS,
            ],
            'Second.Minute.Greater' => [
                Time::of(Time\Hour::of(12), Time\Minute::of(20), Time\Second::of(45, 4545)),
                Time::of(Time\Hour::of(12), Time\Minute::of(15), Time\Second::of(45, 4545)),
                Precision::Second,
                self::GREATER,
            ],
            'Second.Hour.Less' => [
                Time::of(Time\Hour::of(12), Time\Minute::of(15), Time\Second::of(45, 4545)),
                Time::of(Time\Hour::of(20), Time\Minute::of(15), Time\Second::of(45, 4545)),
                Precision::Second,
                self::LESS,
            ],
            'Second.Hour.Greater' => [
                Time::of(Time\Hour::of(20), Time\Minute::of(15), Time\Second::of(45, 4545)),
                Time::of(Time\Hour::of(12), Time\Minute::of(15), Time\Second::of(45, 4545)),
                Precision::Second,
                self::GREATER,
            ],

            // Precision::Minute
            'Minute.Second.Less' => [
                Time::of(Time\Hour::of(12), Time\Minute::of(15), Time\Second::of(45, 4545)),
                Time::of(Time\Hour::of(12), Time\Minute::of(15), Time\Second::of(50, 5555)),
                Precision::Minute,
                self::EQUAL,
            ],
            'Minute.Second.Greater' => [
                Time::of(Time\Hour::of(12), Time\Minute::of(15), Time\Second::of(50, 5555)),
                Time::of(Time\Hour::of(12), Time\Minute::of(15), Time\Second::of(45, 4545)),
                Precision::Minute,
                self::EQUAL,
            ],
            'Minute.Minute.Less' => [
                Time::of(Time\Hour::of(12), Time\Minute::of(15), Time\Second::of(50, 5555)),
                Time::of(Time\Hour::of(12), Time\Minute::of(20), Time\Second::of(45, 4545)),
                Precision::Minute,
                self::LESS,
            ],
            'Minute.Minute.Greater' => [
                Time::of(Time\Hour::of(12), Time\Minute::of(20), Time\Second::of(45, 4545)),
                Time::of(Time\Hour::of(12), Time\Minute::of(15), Time\Second::of(50, 5555)),
                Precision::Minute,
                self::GREATER,
            ],
            'Minute.Hour.Less' => [
                Time::of(Time\Hour::of(12), Time\Minute::of(15), Time\Second::of(50, 5555)),
                Time::of(Time\Hour::of(20), Time\Minute::of(15), Time\Second::of(45, 4545)),
                Precision::Minute,
                self::LESS,
            ],
            'Minute.Hour.Greater' => [
                Time::of(Time\Hour::of(20), Time\Minute::of(15), Time\Second::of(45, 4545)),
                Time::of(Time\Hour::of(12), Time\Minute::of(15), Time\Second::of(50, 5555)),
                Precision::Minute,
                self::GREATER,
            ],
        ];
    }

    #[Depends('testBasic')]
    #[DataProvider('comparisonProvider')]
    public function testComparison(Time $a, Time $b, Precision $precision, int $expected): void
    {
        self::assertTrue($a->is($a));
        self::assertFalse($a->isNot($a));
        self::assertFalse($a->isBefore($a));
        self::assertFalse($a->isAfter($a));
        self::assertTrue($a->isBeforeOrEqualTo($a));
        self::assertTrue($a->isAfterOrEqualTo($a));

        $comparison = $a->compareTo($b, $precision);
        self::assertEquals($expected, $comparison->value());
        self::assertEquals($comparison->less(), $a->isBefore($b, $precision));
        self::assertEquals($comparison->greater(), $a->isAfter($b, $precision));
        self::assertEquals($comparison->equal(), $a->is($b, $precision));
        self::assertEquals($comparison->notEqual(), $a->isNot($b, $precision));
        self::assertEquals($comparison->lessOrEqual(), $a->isBeforeOrEqualTo($b, $precision));
        self::assertEquals($comparison->greaterOrEqual(), $a->isAfterOrEqualTo($b, $precision));
    }

    #[TestWith([[11, 0, 0, 0], [12, 0, 0, 999], ['hours' => 1]])]
    #[TestWith([[11, 0, 0, 999], [12, 0, 0, 0], ['minutes' => 59, 'seconds' => 59]])]
    #[TestWith([[11, 0, 0, 999], [12, 0, 0, 0], ['hours' => 1], Precision::Second])]
    #[TestWith([[9, 15, 30, 999], [12, 10, 45, 0], ['hours' => 2, 'minutes' => 55, 'seconds' => 14]])]
    #[TestWith([[9, 15, 30, 999], [12, 10, 45, 0], ['hours' => 2, 'minutes' => 55, 'seconds' => 15], Precision::Second])]
    #[TestWith([[9, 15, 30, 999], [12, 10, 15, 0], ['hours' => 2, 'minutes' => 54, 'seconds' => 44]])]
    #[TestWith([[9, 15, 30, 999], [12, 10, 15, 0], ['hours' => 2, 'minutes' => 55], Precision::Minute])]
    #[TestWith([[9, 15, 30, 999], [9, 15, 35, 0], ['seconds' => 4]])]
    #[TestWith([[9, 15, 30, 999], [9, 15, 35, 0], ['seconds' => 5], Precision::Second])]
    #[TestWith([[9, 15, 30, 999], [9, 15, 35, 0], ['seconds' => 0], Precision::Minute])]
    #[TestWith([[9, 15, 30, 0], [9, 15, 30, 0], ['seconds' => 0]])]
    #[TestWith([[12, 0, 35, 999], [12, 0, 35, 0], ['seconds' => 0]])]
    #[TestWith([[12, 0, 35, 999], [12, 0, 35, 0], ['seconds' => 0], Precision::Second])]
    #[TestWith([[12, 0, 35, 999], [12, 0, 35, 0], ['seconds' => 0], Precision::Minute])]
    #[TestWith([[12, 0, 35, 999], [12, 0, 30, 0], ['seconds' => 0]])]
    #[DependsOnClass(DurationTest::class)]
    #[Depends('testBasic')]
    public function testUntil(array $since, array $till, array $expected, Precision $precision = Precision::Micro): void
    {
        $expected = Duration::of(...$expected);
        $since = Time::of($since[0], $since[1], Time\Second::of($since[2], $since[3]));
        $till = Time::of($till[0], $till[1], Time\Second::of($till[2], $till[3]));

        $actual = $since->until($till, $precision);

        self::assertEquals($expected, $actual);
    }

    #[TestWith([[11, 0, 0, 0], [12, 0, 0, 999], ['hours' => 1]])]
    #[TestWith([[11, 0, 0, 999], [12, 0, 0, 0], ['minutes' => 59, 'seconds' => 59]])]
    #[TestWith([[11, 0, 0, 999], [12, 0, 0, 0], ['hours' => 1], Precision::Second])]
    #[TestWith([[9, 15, 30, 999], [12, 10, 45, 0], ['hours' => 2, 'minutes' => 55, 'seconds' => 14]])]
    #[TestWith([[9, 15, 30, 999], [12, 10, 45, 0], ['hours' => 2, 'minutes' => 55, 'seconds' => 15], Precision::Second])]
    #[TestWith([[9, 15, 30, 999], [12, 10, 15, 0], ['hours' => 2, 'minutes' => 54, 'seconds' => 44]])]
    #[TestWith([[9, 15, 30, 999], [12, 10, 15, 0], ['hours' => 2, 'minutes' => 55], Precision::Minute])]
    #[TestWith([[9, 15, 30, 999], [9, 15, 35, 0], ['seconds' => 4]])]
    #[TestWith([[9, 15, 30, 999], [9, 15, 35, 0], ['seconds' => 5], Precision::Second])]
    #[TestWith([[9, 15, 30, 999], [9, 15, 35, 0], ['seconds' => 0], Precision::Minute])]
    #[TestWith([[9, 15, 30, 0], [9, 15, 30, 0], ['seconds' => 0]])]
    #[TestWith([[12, 0, 35, 999], [12, 0, 35, 0], ['seconds' => 0]])]
    #[TestWith([[12, 0, 35, 999], [12, 0, 35, 0], ['seconds' => 0], Precision::Second])]
    #[TestWith([[12, 0, 35, 999], [12, 0, 35, 0], ['seconds' => 0], Precision::Minute])]
    #[TestWith([[12, 0, 35, 999], [12, 0, 30, 0], ['seconds' => 5]])]
    #[TestWith([[12, 10, 15, 0], [12, 0, 30, 999], ['minutes' => 9, 'seconds' => 44]])]
    #[TestWith([[12, 10, 15, 0], [12, 0, 30, 999], ['minutes' => 9, 'seconds' => 45], Precision::Second])]
    #[TestWith([[12, 10, 15, 0], [12, 0, 30, 999], ['minutes' => 10], Precision::Minute])]
    #[TestWith([[12, 10, 15, 0], [6, 0, 30, 999], ['hours' => 6, 'minutes' => 10], Precision::Minute])]
    #[DependsOnClass(DurationTest::class)]
    #[Depends('testBasic')]
    public function testDifference(
        array $since,
        array $till,
        array $expected,
        Precision $precision = Precision::Micro,
    ): void {
        $expected = Duration::of(...$expected);
        $since = Time::of($since[0], $since[1], Time\Second::of($since[2], $since[3]));
        $till = Time::of($till[0], $till[1], Time\Second::of($till[2], $till[3]));

        $actual = $since->difference($till, $precision);

        self::assertEquals($expected, $actual);
    }
}
