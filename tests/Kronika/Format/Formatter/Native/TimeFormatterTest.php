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

use Kronika\Format\Formatter\Native\TimeFormatter;
use Kronika\Format\Parsed;
use Kronika\Format\Time\Formatted;
use Kronika\Time;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\TestWith;
use PHPUnit\Framework\TestCase;

#[CoversClass(TimeFormatter::class)]
final class TimeFormatterTest extends TestCase
{
    private static TimeFormatter $formatter;

    #[\Override]
    public static function setUpBeforeClass(): void
    {
        self::$formatter = new TimeFormatter();
    }

    // 09:05:04.000222
    #[TestWith(['H:i:s.u', '09:05:04.000222'])]
    #[TestWith(['H:i:s', '09:05:04'])]
    #[TestWith(['H:i', '09:05'])]
    #[TestWith(['H', '09'])]
    #[TestWith(['H:\i:s', '09:i:04'])]
    #[TestWith(['H:i:\s.u\P', '09:05:s.000222P'])]
    #[TestWith(['a', 'am'])]
    #[TestWith(['A', 'AM'])]
    #[TestWith(['B', '420'])]
    #[TestWith(['g', '9'])]
    #[TestWith(['G', '9'])]
    #[TestWith(['h', '09'])]
    #[TestWith(['v', '000'])]
    #[TestWith(['Y-m-d', 'Y-m-d'])]
    #[TestWith(['Y m d', 'Y m d'])]
    #[TestWith(['d m \Y', 'd m Y'])]
    #[TestWith(['l, d-M-Y', 'l, d-M-Y'])]
    #[TestWith(['D, d M y', 'D, d M y'])]
    #[TestWith(['Y-m-d\TH:i:s.uP', 'Y-m-dT09:05:04.000222P'])]
    #[TestWith(['Y-m-d\TH:i:sO', 'Y-m-dT09:05:04O'])]
    #[TestWith(['l, d-M-Y H:i:s T', 'l, d-M-Y 09:05:04 T'])]
    #[TestWith(['D, d M y H:i:s O', 'D, d M y 09:05:04 O'])]
    #[TestWith(['Y-m-d\TH:i:s.vPO', 'Y-m-dT09:05:04.000PO'])]
    #[TestWith(['D, d M Y H:i:s \G\M\T', 'D, d M Y 09:05:04 GMT'])]
    #[TestWith(['c', 'c'])]
    #[TestWith(['r', 'r'])]
    #[TestWith(['U', 'U'])]
    public function testFormat(string $format, string $expected): void
    {
        static $time = Time::of(9, 5, Time\Second::of(4, 222));

        $actual = self::$formatter->format($time, $format);

        self::assertEquals($expected, $actual);
    }

    // 14:07:12.123456
    #[TestWith(['H:i:s.u', '14:07:12.123456'])]
    #[TestWith(['h:i:s.u A', '02:07:12.123456 PM'])]
    #[TestWith(['H:i:s.u\P', '14:07:12.123456P'])]
    #[TestWith(['Y-m-d\TH:i:s.uP', 'Y-m-dT14:07:12.123456P'])]
    public function testParse(string $format, string $value): void
    {
        $expected = new Parsed(time: new Parsed\ParsedTime(hour: 14, minute: 7, second: 12, micro: 123456));
        $actual = self::$formatter->parse(new Formatted($format, $value));

        self::assertEquals($expected, $actual);
    }
}
