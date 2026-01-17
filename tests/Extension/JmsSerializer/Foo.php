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

namespace Kronika\Extension\Tests\JmsSerializer;

use JMS\Serializer\Annotation\Type;
use Kronika\Date;
use Kronika\Duration;
use Kronika\Instant;
use Kronika\LocalDateTime;
use Kronika\Time;
use Kronika\ZonedDateTime;

final readonly class Foo
{
    public function __construct(
        #[Type(Date::class)]
        public Date $date,
        #[Type(Date::class . "<'F jS, Y'>")]
        public Date $dateFormatted,
        #[Type('KronikaDate')]
        public Date $dateAlias,
        #[Type("KronikaDate<'F jS, Y'>")]
        public Date $dateAliasFormatted,

        #[Type(Date\Year::class)]
        public Date\Year $year,
        #[Type('KronikaYear')]
        public Date\Year $yearAlias,

        #[Type(Date\Month::class)]
        public Date\Month $month,
        #[Type('KronikaMonth')]
        public Date\Month $monthAlias,

        #[Type(Date\DayOfWeek::class)]
        public Date\DayOfWeek $dayOfWeek,
        #[Type('KronikaDayOfWeek')]
        public Date\DayOfWeek $dayOfWeekAlias,

        #[Type(Date\DayOfYear::class)]
        public Date\DayOfYear $dayOfYear,
        #[Type('KronikaDayOfYear')]
        public Date\DayOfYear $dayOfYearAlias,

        #[Type(Date\DayOfMonth::class)]
        public Date\DayOfMonth $dayOfMonth,
        #[Type('KronikaDayOfMonth')]
        public Date\DayOfMonth $dayOfMonthAlias,

        #[Type(Time::class)]
        public Time $time,
        #[Type(Time::class . "<'H/i/s'>")]
        public Time $timeFormatted,
        #[Type('KronikaTime')]
        public Time $timeAlias,
        #[Type("KronikaTime<'H/i/s'>")]
        public Time $timeAliasFormatted,

        #[Type(Time\Hour::class)]
        public Time\Hour $hour,
        #[Type('KronikaHour')]
        public Time\Hour $hourAlias,

        #[Type(Time\Minute::class)]
        public Time\Minute $minute,
        #[Type('KronikaMinute')]
        public Time\Minute $minuteAlias,

        #[Type(Time\Second::class)]
        public Time\Second $second,
        #[Type('KronikaSecond')]
        public Time\Second $secondAlias,

        #[Type(Duration::class . "<'in_seconds'>")]
        public Duration $durationInSeconds,
        #[Type(Duration::class . "<'in_hours'>")]
        public Duration $durationInHours,
        #[Type(Duration::class . "<'array'>")]
        public Duration $durationArray,
        #[Type(Duration::class . "<'time_interval'>")]
        public Duration $durationTimeInterval,
        #[Type('KronikaDuration')]
        public Duration $durationAlias,

        #[Type(Instant::class)]
        public Instant $instant,
        #[Type('KronikaInstant')]
        public Instant $instantAlias,

        #[Type(LocalDateTime::class)]
        public LocalDateTime $localDateTime,
        #[Type(LocalDateTime::class . "<'F jS, Y, H:i:s'>")]
        public LocalDateTime $localDateTimeFormatted,
        #[Type('KronikaLocalDateTime')]
        public LocalDateTime $localDateTimeAlias,
        #[Type("KronikaLocalDateTime<'F jS, Y, H:i'>")]
        public LocalDateTime $localDateTimeAliasFormatted,

        #[Type(ZonedDateTime::class)]
        public ZonedDateTime $zonedDateTime,
        #[Type(ZonedDateTime::class . "<'" . \DateTimeInterface::RSS . "'>")]
        public ZonedDateTime $zonedDateTimeFormatted,
        #[Type(ZonedDateTime::class . "<'U'>")]
        public ZonedDateTime $zonedDateTimeTs,
        #[Type(ZonedDateTime::class . "<'U.u'>")]
        public ZonedDateTime $zonedDateTimeTsMicro,
        #[Type('KronikaZonedDateTime')]
        public ZonedDateTime $zonedDateTimeAlias,
        #[Type("KronikaZonedDateTime<'" . \DateTimeInterface::RSS . "'>")]
        public ZonedDateTime $zonedDateTimeAliasFormatted,

        #[Type(\DateTimeImmutable::class . "<'" . \DateTimeInterface::ATOM . "'>")]
        public \DateTimeImmutable $nativeDateTime,
    ) {
    }
}
