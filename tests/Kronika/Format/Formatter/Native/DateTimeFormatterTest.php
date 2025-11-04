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

namespace Kronika\Tests\Format\Formatter\Native;

use Kronika\Date;
use Kronika\Format\DateTime\FormattedLocal;
use Kronika\Format\DateTime\FormattedZoned;
use Kronika\Format\Formatter\Native\DateTimeFormatter;
use Kronika\Format\Parsed;
use Kronika\LocalDateTime;
use Kronika\Time;
use Kronika\ZonedDateTime;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\TestWith;
use PHPUnit\Framework\TestCase;

#[CoversClass(DateTimeFormatter::class)]
final class DateTimeFormatterTest extends TestCase
{
    private static DateTimeFormatter $formatter;

    #[\Override]
    public static function setUpBeforeClass(): void
    {
        self::$formatter = new DateTimeFormatter();
    }

    // 2025-07-09 14:03:02.123456 +01:30, Wednesday
    #[TestWith(['H:i:s.u', '14:03:02.123456'])]
    #[TestWith(['H:i:s', '14:03:02'])]
    #[TestWith(['H:i', '14:03'])]
    #[TestWith(['H', '14'])]
    #[TestWith(['H:\i:s', '14:i:02'])]
    #[TestWith(['H:i:\s.u\P', '14:03:s.123456P'])]
    #[TestWith(['H:i:\s.uP', '14:03:s.123456+01:30'])]
    #[TestWith(['a', 'pm'])]
    #[TestWith(['A', 'PM'])]
    #[TestWith(['B', '564'])]
    #[TestWith(['g', '2'])]
    #[TestWith(['G', '14'])]
    #[TestWith(['h', '02'])]
    #[TestWith(['v', '123'])]
    #[TestWith(['Y-m-d', '2025-07-09'])]
    #[TestWith(['Y m d', '2025 07 09'])]
    #[TestWith(['d m \Y', '09 07 Y'])]
    #[TestWith(['l, d-M-Y', 'Wednesday, 09-Jul-2025'])]
    #[TestWith(['D, d M y', 'Wed, 09 Jul 25'])]
    #[TestWith(['N', '3'])]
    #[TestWith(['w', '3'])]
    #[TestWith(['j', '9'])]
    #[TestWith(['S', 'th'])]
    #[TestWith(['F', 'July'])]
    #[TestWith(['n', '7'])]
    #[TestWith(['t', '31'])]
    #[TestWith(['L', '0'])]
    #[TestWith(['o', '2025'])]
    #[TestWith(['X', '+2025'])]
    #[TestWith(['x', '2025'])]
    #[TestWith(['H:i:\s.u\P', '14:03:s.123456P'])]
    #[TestWith(['Y-m-d\TH:i:s.uP', '2025-07-09T14:03:02.123456+01:30'])]
    #[TestWith(['Y-m-d\TH:i:sO', '2025-07-09T14:03:02+0130'])]
    #[TestWith(['l, d-M-Y H:i:s T', 'Wednesday, 09-Jul-2025 14:03:02 GMT+0130'])]
    #[TestWith(['D, d M y H:i:s O', 'Wed, 09 Jul 25 14:03:02 +0130'])]
    #[TestWith(['Y-m-d\TH:i:s.vPO', '2025-07-09T14:03:02.123+01:30+0130'])]
    #[TestWith(['D, d M Y H:i:s \G\M\T', 'Wed, 09 Jul 2025 14:03:02 GMT'])]
    #[TestWith(['c', '2025-07-09T14:03:02+01:30'])]
    #[TestWith(['r', 'Wed, 09 Jul 2025 14:03:02 +0130'])]
    #[TestWith(['U', '1752064382'])]
    #[TestWith(['U.u', '1752064382.123456'])]
    public function testFormatZoned(string $format, string $expected): void
    {
        static $datetime = ZonedDateTime::of(
            Date::of(2025, 7, 9),
            Time::of(14, 3, Time\Second::of(2, 123456)),
            new \DateTimeZone('+01:30'),
        );

        $actual = self::$formatter->format($datetime, $format);

        self::assertEquals($expected, $actual);
    }

    // 2025-07-09 14:03:02.123456, Wednesday
    #[TestWith(['H:i:s.u', '14:03:02.123456'])]
    #[TestWith(['H:i:s', '14:03:02'])]
    #[TestWith(['H:i', '14:03'])]
    #[TestWith(['H', '14'])]
    #[TestWith(['H:\i:s', '14:i:02'])]
    #[TestWith(['H:i:\s.u\P', '14:03:s.123456P'])]
    #[TestWith(['H:i:\s.uP', '14:03:s.123456'])]
    #[TestWith(['a', 'pm'])]
    #[TestWith(['A', 'PM'])]
    #[TestWith(['B', '627'])]
    #[TestWith(['g', '2'])]
    #[TestWith(['G', '14'])]
    #[TestWith(['h', '02'])]
    #[TestWith(['v', '123'])]
    #[TestWith(['Y-m-d', '2025-07-09'])]
    #[TestWith(['Y m d', '2025 07 09'])]
    #[TestWith(['d m \Y', '09 07 Y'])]
    #[TestWith(['l, d-M-Y', 'Wednesday, 09-Jul-2025'])]
    #[TestWith(['D, d M y', 'Wed, 09 Jul 25'])]
    #[TestWith(['N', '3'])]
    #[TestWith(['w', '3'])]
    #[TestWith(['j', '9'])]
    #[TestWith(['S', 'th'])]
    #[TestWith(['F', 'July'])]
    #[TestWith(['n', '7'])]
    #[TestWith(['t', '31'])]
    #[TestWith(['L', '0'])]
    #[TestWith(['o', '2025'])]
    #[TestWith(['X', '+2025'])]
    #[TestWith(['x', '2025'])]
    #[TestWith(['H:i:\s.u\P', '14:03:s.123456P'])]
    #[TestWith(['Y-m-d\TH:i:s.uP', '2025-07-09T14:03:02.123456'])]
    #[TestWith(['Y-m-d\TH:i:sO', '2025-07-09T14:03:02'])]
    #[TestWith(['l, d-M-Y H:i:s T', 'Wednesday, 09-Jul-2025 14:03:02'])]
    #[TestWith(['D, d M y H:i:s O', 'Wed, 09 Jul 25 14:03:02'])]
    #[TestWith(['Y-m-d\TH:i:s.vPO', '2025-07-09T14:03:02.123'])]
    #[TestWith(['D, d M Y H:i:s \G\M\T', 'Wed, 09 Jul 2025 14:03:02 GMT'])]
    #[TestWith(['U', '1752069782'])]
    #[TestWith(['U.u', '1752069782.123456'])]
    public function testFormatLocal(string $format, string $expected): void
    {
        static $datetime = LocalDateTime::of(
            Date::of(2025, 7, 9),
            Time::of(14, 3, Time\Second::of(2, 123456)),
        );

        $actual = self::$formatter->format($datetime, $format);

        self::assertEquals($expected, $actual);
    }

    // 2025-07-09 14:03:02.123456 +01:30, Wednesday
    #[TestWith(['Y-m-d\TH:i:s.uP', '2025-07-09T14:03:02.123456+01:30'])]
    #[TestWith(['Y-m-d\TH:i:s.uO', '2025-07-09T14:03:02.123456+0130'])]
    #[TestWith(['l, d-M-Y H:i:s.u T', 'Wednesday, 09-Jul-2025 14:03:02.123456 GMT+0130'])]
    #[TestWith(['D, d M y H:i:s.u O', 'Wed, 09 Jul 25 14:03:02.123456 +0130'])]
    #[TestWith(['Y-m-d\TH:i:s.uPO', '2025-07-09T14:03:02.123456+01:30+0130'])]
    #[TestWith(['D, d M Y H:i:s.u \G\M\TO', 'Wed, 09 Jul 2025 14:03:02.123456 GMT+0130'])]
    #[TestWith(['U.u', '1752064382.123456'])]
    public function testParseZoned(string $format, string $value): void
    {
        $isUtc = $format === 'U.u';
        $expected = new Parsed(
            year: 2025, month: 7, day: 9,
            hour: $isUtc ? 12 : 14,
            minute: $isUtc ? 33 : 3,
            second: 2,
            micro: 123456,
            timezone: $isUtc ? '+00:00' : '+01:30',
        );
        $actual = self::$formatter->parse(new FormattedZoned($format, $value));

        self::assertEquals($expected, $actual);
    }

    // 2025-07-09 14:03:02.123456, Wednesday
    #[TestWith(['Y-m-d\TH:i:s.u', '2025-07-09T14:03:02.123456'])]
    #[TestWith(['l, d-M-Y H:i:s.u', 'Wednesday, 09-Jul-2025 14:03:02.123456'])]
    #[TestWith(['D, d M y H:i:s.u', 'Wed, 09 Jul 25 14:03:02.123456'])]
    #[TestWith(['D, d M Y H:i:s.u \G\M\T', 'Wed, 09 Jul 2025 14:03:02.123456 GMT'])]
    #[TestWith(['U.u', '1752064382.123456'])]
    #[TestWith(['Y-m-d\TH:i:s.uP', '2025-07-09T14:03:02.123456'])]
    #[TestWith(['Y-m-d\TH:i:s.uO', '2025-07-09T14:03:02.123456'])]
    #[TestWith(['Y-m-d\TH:i:s.uP', '2025-07-09T14:03:02.123456+01:30'])]
    #[TestWith(['Y-m-d\TH:i:s.uO', '2025-07-09T14:03:02.123456+0130'])]
    public function testParseLocal(string $format, string $value): void
    {
        $isUtc = $format === 'U.u';
        $expected = new Parsed(
            year: 2025, month: 7, day: 9,
            hour: $isUtc ? 12 : 14,
            minute: $isUtc ? 33 : 3,
            second: 2,
            micro: 123456,
        );
        $actual = self::$formatter->parse(new FormattedLocal($format, $value));

        self::assertEquals($expected->year(), $actual->year());
        self::assertEquals($expected->month(), $actual->month());
        self::assertEquals($expected->day(), $actual->day());
        self::assertEquals($expected->hour(), $actual->hour());
        self::assertEquals($expected->minute(), $actual->minute());
        self::assertEquals($expected->second(), $actual->second());
    }
}
