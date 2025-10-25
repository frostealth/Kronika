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
        #[Type(Date\Year::class)]
        public Date\Year $year,
        #[Type(Date\Month::class)]
        public Date\Month $month1,
        #[Type('KronikaMonth')]
        public Date\Month $month2,
        #[Type(Date\DayOfWeek::class)]
        public Date\DayOfWeek $dayOfWeek1,
        #[Type('KronikaDayOfWeek')]
        public Date\DayOfWeek $dayOfWeek2,
        #[Type(Date\DayOfMonth::class)]
        public Date\DayOfMonth $dayOfMonth,
        #[Type(Time::class)]
        public Time $time,
        #[Type(Time\Hour::class)]
        public Time\Hour $hour,
        #[Type(Time\Minute::class)]
        public Time\Minute $minute,
        #[Type(Time\Second::class)]
        public Time\Second $second,
        #[Type(Duration::class)]
        public Duration $duration,
        #[Type(Instant::class)]
        public Instant $instant,
        #[Type(LocalDateTime::class)]
        public LocalDateTime $localDateTime,
        #[Type(ZonedDateTime::class)]
        public ZonedDateTime $zonedDateTime,
        #[Type(\DateTimeImmutable::class . "<'" . \DateTimeInterface::ATOM . "'>")]
        public \DateTimeImmutable $nativeDateTime,
    ) {
    }
}
