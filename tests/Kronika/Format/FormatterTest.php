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

namespace Kronika\Tests\Format;

use Kronika\Date;
use Kronika\Format;
use Kronika\Format\Formatter;
use Kronika\LocalDateTime;
use Kronika\Tests\LocalDateTimeTest;
use Kronika\Tests\ZonedDateTimeTest;
use Kronika\Time;
use Kronika\Time\Second;
use Kronika\ZonedDateTime;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DependsOnClass;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

#[CoversClass(Formatter::class)]
final class FormatterTest extends TestCase
{
    private Formatter $formatter;
    private MockObject $datetime;
    private MockObject $date;
    private MockObject $time;

    #[\Override]
    protected function setUp(): void
    {
        $this->formatter = new Formatter(
            datetime: $this->datetime = $this->createMock(Format\DateTime\Formatter::class),
            date: $this->date = $this->createMock(Format\Date\Formatter::class),
            time: $this->time = $this->createMock(Format\Time\Formatter::class),
        );
    }

    #[DependsOnClass(ZonedDateTimeTest::class)]
    public function testFormatZoned(): void
    {
        $datetime = ZonedDateTime::of(
            Date::of(2025, 5, 9),
            Time::of(7, 6, Second::of(2, 4444)),
            new \DateTimeZone('+01:30'),
        );
        $format = 'Y-m-d\TH:i:s.uP';

        $this->datetime->expects($this->once())->method('format')->with($datetime, $format)->willReturn('str');
        $this->datetime->expects($this->never())->method('parse');
        $this->date->expects($this->never())->method('format');
        $this->date->expects($this->never())->method('parse');
        $this->time->expects($this->never())->method('format');
        $this->time->expects($this->never())->method('parse');

        self::assertEquals('str', $this->formatter->format($datetime, $format));
    }

    #[DependsOnClass(LocalDateTimeTest::class)]
    public function testFormatLocal(): void
    {
        $datetime = LocalDateTime::of(
            Date::of(2025, 5, 9),
            Time::of(7, 6, Second::of(2, 4444)),
        );
        $format = 'Y-m-d\TH:i:s.uP';

        $this->datetime->expects($this->once())->method('format')->with($datetime, $format)->willReturn('str');
        $this->datetime->expects($this->never())->method('parse');
        $this->date->expects($this->never())->method('format');
        $this->date->expects($this->never())->method('parse');
        $this->time->expects($this->never())->method('format');
        $this->time->expects($this->never())->method('parse');

        self::assertEquals('str', $this->formatter->format($datetime, $format));
    }

    #[DependsOnClass(LocalDateTimeTest::class)]
    public function testFormatDate(): void
    {
        $date = Date::of(2025, 5, 9);
        $format = 'Y-m-d';

        $this->datetime->expects($this->never())->method('format');
        $this->datetime->expects($this->never())->method('parse');
        $this->date->expects($this->once())->method('format')->with($date, $format)->willReturn('str');
        $this->date->expects($this->never())->method('parse');
        $this->time->expects($this->never())->method('format');
        $this->time->expects($this->never())->method('parse');

        self::assertEquals('str', $this->formatter->format($date, $format));
    }

    #[DependsOnClass(LocalDateTimeTest::class)]
    public function testFormatTime(): void
    {
        $date = Time::of(12, 15, 30);
        $format = 'H:i:s';

        $this->datetime->expects($this->never())->method('format');
        $this->datetime->expects($this->never())->method('parse');
        $this->date->expects($this->never())->method('format');
        $this->date->expects($this->never())->method('parse');
        $this->time->expects($this->once())->method('format')->with($date, $format)->willReturn('str');
        $this->time->expects($this->never())->method('parse');

        self::assertEquals('str', $this->formatter->format($date, $format));
    }

    public function testParseZoned(): void
    {
        $format = 'Y-m-d\TH:i:s.uP';
        $value = '2025-12-31T12:15:30.000444+01:30';
        $formatted = new Format\DateTime\FormattedZoned($format, $value);
        $parsed = new Format\Parsed();

        $this->datetime->expects($this->never())->method('format');
        $this->datetime->expects($this->once())->method('parse')->with($formatted)->willReturn($parsed);
        $this->date->expects($this->never())->method('format');
        $this->date->expects($this->never())->method('parse');
        $this->time->expects($this->never())->method('format');
        $this->time->expects($this->never())->method('parse');

        $this->formatter->parse($formatted);
    }

    public function testParseLocal(): void
    {
        $format = 'Y-m-d\TH:i:s.u';
        $value = '2025-12-31T12:15:30.000444';
        $formatted = new Format\DateTime\FormattedLocal($format, $value);
        $parsed = new Format\Parsed();

        $this->datetime->expects($this->never())->method('format');
        $this->datetime->expects($this->once())->method('parse')->with($formatted)->willReturn($parsed);
        $this->date->expects($this->never())->method('format');
        $this->date->expects($this->never())->method('parse');
        $this->time->expects($this->never())->method('format');
        $this->time->expects($this->never())->method('parse');

        $this->formatter->parse($formatted);
    }

    public function testParseDate(): void
    {
        $format = 'Y-m-d';
        $value = '2025-12-31';
        $formatted = new Format\Date\Formatted($format, $value);
        $parsed = new Format\Parsed();

        $this->datetime->expects($this->never())->method('format');
        $this->datetime->expects($this->never())->method('parse');
        $this->date->expects($this->never())->method('format');
        $this->date->expects($this->once())->method('parse')->with($formatted)->willReturn($parsed);
        $this->time->expects($this->never())->method('format');
        $this->time->expects($this->never())->method('parse');

        $this->formatter->parse($formatted);
    }

    public function testParseTime(): void
    {
        $format = 'H:i:s.u';
        $value = '12:15:30.000555';
        $formatted = new Format\Time\Formatted($format, $value);
        $parsed = new Format\Parsed();

        $this->datetime->expects($this->never())->method('format');
        $this->datetime->expects($this->never())->method('parse');
        $this->date->expects($this->never())->method('format');
        $this->date->expects($this->never())->method('parse');
        $this->time->expects($this->never())->method('format');
        $this->time->expects($this->once())->method('parse')->with($formatted)->willReturn($parsed);

        $this->formatter->parse($formatted);
    }
}
