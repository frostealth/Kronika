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
use Kronika\Format\Date\Formatted;
use Kronika\Format\Formatter\Native\DateFormatter;
use Kronika\Format\Parsed;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\TestWith;
use PHPUnit\Framework\TestCase;

#[CoversClass(DateFormatter::class)]
final class DateFormatterTest extends TestCase
{
    private static DateFormatter $formatter;

    #[\Override]
    public static function setUpBeforeClass(): void
    {
        self::$formatter = new DateFormatter();
    }

    // 2025-07-09, Wednesday
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
    #[TestWith(['H:i:\s.u\P', 'H:i:s.uP'])]
    #[TestWith(['Y-m-d\TH:i:s.uP', '2025-07-09TH:i:s.uP'])]
    #[TestWith(['Y-m-d\TH:i:sO', '2025-07-09TH:i:sO'])]
    #[TestWith(['l, d-M-Y H:i:s T', 'Wednesday, 09-Jul-2025 H:i:s T'])]
    #[TestWith(['D, d M y H:i:s O', 'Wed, 09 Jul 25 H:i:s O'])]
    #[TestWith(['Y-m-d\TH:i:s.vPO', '2025-07-09TH:i:s.vPO'])]
    #[TestWith(['D, d M Y H:i:s \G\M\T', 'Wed, 09 Jul 2025 H:i:s GMT'])]
    #[TestWith(['c', 'c'])]
    #[TestWith(['r', 'r'])]
    #[TestWith(['U', 'U'])]
    public function testFormat(string $format, string $expected): void
    {
        static $date = Date::of(2025, 7, 9);

        $actual = self::$formatter->format($date, $format);

        self::assertEquals($expected, $actual);
    }

    // 2025-07-09, Friday
    #[TestWith(['Y-m-d', '2025-07-09'])]
    #[TestWith(['Y m d', '2025 07 09'])]
    #[TestWith(['d m Y \Y', '09 07 2025 Y'])]
    #[TestWith(['l, d-M-Y', 'Wednesday, 09-Jul-2025'])]
    #[TestWith(['D, d M y', 'Wed, 09 Jul 25'])]
    #[TestWith(['Y-m-d\TH:i:s.uP', '2025-07-09TH:i:s.uP'])]
    #[TestWith(['Y-m-d\TH:i:sO', '2025-07-09TH:i:sO'])]
    #[TestWith(['l, d-M-Y H:i:s T', 'Wednesday, 09-Jul-2025 H:i:s T'])]
    #[TestWith(['D, d M y H:i:s O', 'Wed, 09 Jul 25 H:i:s O'])]
    #[TestWith(['Y-m-d\TH:i:s.vPO', '2025-07-09TH:i:s.vPO'])]
    #[TestWith(['D, d M Y H:i:s \G\M\T', 'Wed, 09 Jul 2025 H:i:s GMT'])]
    public function testParse(string $format, string $value): void
    {
        $expected = new Parsed(date: new Parsed\ParsedDate(year: 2025, month: 7, day: 9));
        $actual = self::$formatter->parse(new Formatted($format, $value));

        self::assertEquals($expected, $actual);
    }
}
