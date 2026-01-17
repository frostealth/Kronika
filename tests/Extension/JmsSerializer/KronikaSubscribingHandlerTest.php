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

use JMS\Serializer\Handler\HandlerRegistry;
use JMS\Serializer\Naming\IdenticalPropertyNamingStrategy;
use JMS\Serializer\Naming\SerializedNameAnnotationStrategy;
use JMS\Serializer\SerializationContext;
use JMS\Serializer\Serializer;
use JMS\Serializer\SerializerBuilder;
use JMS\Serializer\Visitor\Factory\JsonSerializationVisitorFactory;
use Kronika\Date;
use Kronika\Duration;
use Kronika\Extension\JmsSerializer\KronikaSubscribingHandler;
use Kronika\Instant;
use Kronika\LocalDateTime;
use Kronika\Time;
use Kronika\ZonedDateTime;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(KronikaSubscribingHandler::class)]
final class KronikaSubscribingHandlerTest extends TestCase
{
    private Serializer $serializer;
    private Foo $entry;

    #[\Override]
    protected function setUp(): void
    {
        $this->serializer = SerializerBuilder::create()
            ->setPropertyNamingStrategy(
                new SerializedNameAnnotationStrategy(
                    new IdenticalPropertyNamingStrategy(),
                ),
            )
            ->setSerializationVisitor(
                'json',
                new JsonSerializationVisitorFactory()
                    ->setOptions(
                        JSON_UNESCAPED_UNICODE
                        | JSON_PRESERVE_ZERO_FRACTION
                        | JSON_PRETTY_PRINT,
                    ),
            )
            ->setSerializationContextFactory(fn() => new SerializationContext()->setSerializeNull(true))
            ->addDefaultDeserializationVisitors()
            ->addDefaultHandlers()
            ->configureHandlers(function (HandlerRegistry $registry) {
                $registry->registerSubscribingHandler(new KronikaSubscribingHandler());
            })
            ->build()
        ;

        $this->entry = new Foo(
            date: Date::of(2025, 12, 31),
            dateFormatted: Date::of(2025, 12, 30),
            dateAlias: Date::of(2025, 12, 29),
            dateAliasFormatted: Date::of(2025, 12, 28),
            year: Date\Year::of(2000),
            yearAlias: Date\Year::of(1999),
            month: Date\Month::January,
            monthAlias: Date\Month::of(2),
            dayOfMonth: Date\DayOfMonth::of(25),
            dayOfMonthAlias: Date\DayOfMonth::of(24),
            dayOfWeek: Date\DayOfWeek::Sunday,
            dayOfWeekAlias: Date\DayOfWeek::Monday,
            dayOfYear: Date\DayOfYear::of(245),
            dayOfYearAlias: Date\DayOfYear::of(302),
            time: Time::of(12, 35, Time\Second::of(55, 999)),
            timeFormatted:Time::of(12, 30, Time\Second::of(50, 555)),
            timeAlias: Time::of(12, 25, Time\Second::of(45, 455)),
            timeAliasFormatted: Time::of(12, 20, Time\Second::of(40, 400)),
            hour: Time\Hour::of(9),
            hourAlias: Time\Hour::of(8),
            minute: Time\Minute::of(55),
            minuteAlias: Time\Minute::of(50),
            second: Time\Second::of(45, 6789),
            secondAlias: Time\Second::of(40, 5555),
            durationInSeconds: Duration::of(days: 1, hours: 2, minutes: 25, seconds: 99),
            durationInHours: Duration::of(days: 1, hours: 2, minutes: 25, seconds: 99),
            durationArray: Duration::of(days: 1, hours: 2, minutes: 25, seconds: 99, micros: 123),
            durationTimeInterval: Duration::of(days: 1, hours: 2, minutes: 25, seconds: 99, micros: 123),
            durationAlias: Duration::of(days: 1, hours: 1, minutes: 25, seconds: 80),
            instant: Instant::of(123456789, 54321),
            instantAlias: Instant::of(123456780, 54310),
            localDateTime: LocalDateTime::of(Date::of(1985, 10, 31), Time::midnight()),
            localDateTimeFormatted: LocalDateTime::of(Date::of(1985, 10, 30), Time::midday()),
            localDateTimeAlias: LocalDateTime::of(Date::of(1985, 10, 29), Time::endOfDay()),
            localDateTimeAliasFormatted: LocalDateTime::of(Date::of(1985, 10, 28), Time::midnight()),
            zonedDateTime: ZonedDateTime::of(Date::of(1990, 9, 5), Time::midday(), new \DateTimeZone('+01:00')),
            zonedDateTimeFormatted: ZonedDateTime::of(Date::of(1990, 9, 4), Time::midday(), new \DateTimeZone('+02:00')),
            zonedDateTimeTs: ZonedDateTime::of(Date::of(1990, 9, 3), Time::midday(), new \DateTimeZone('+02:30')),
            zonedDateTimeTsMicro: ZonedDateTime::of(Date::of(1990, 9, 2), Time::endOfDay(), new \DateTimeZone('+03:00')),
            zonedDateTimeAlias: ZonedDateTime::of(Date::of(1990, 9, 1), Time::midday(), new \DateTimeZone('+03:00')),
            zonedDateTimeAliasFormatted: ZonedDateTime::of(Date::of(1990, 8, 31), Time::endOfDay(), new \DateTimeZone('+04:00')),
            nativeDateTime: new \DateTimeImmutable('2000-05-24 11:30:30'),
        );
    }

    public function testSerializeAndDeserialize(): void
    {
        $serialized = $this->serializer->toArray($this->entry);
        $unserialized = $this->serializer->fromArray($serialized, Foo::class);

        self::assertEquals($this->entry->date, $unserialized->date);
        self::assertEquals($this->entry->dateFormatted, $unserialized->dateFormatted);
        self::assertEquals($this->entry->dateFormatted->format('F jS, Y'), $serialized['dateFormatted']);
        self::assertEquals($this->entry->dateAlias, $unserialized->dateAlias);
        self::assertEquals($this->entry->dateAliasFormatted, $unserialized->dateAliasFormatted);
        self::assertEquals($this->entry->dateAliasFormatted->format('F jS, Y'), $serialized['dateAliasFormatted']);

        self::assertEquals($this->entry->year, $unserialized->year);
        self::assertEquals($this->entry->yearAlias, $unserialized->yearAlias);
        self::assertEquals($this->entry->month, $unserialized->month);
        self::assertEquals($this->entry->monthAlias, $unserialized->monthAlias);
        self::assertEquals($this->entry->dayOfMonth, $unserialized->dayOfMonth);
        self::assertEquals($this->entry->dayOfMonthAlias, $unserialized->dayOfMonthAlias);
        self::assertEquals($this->entry->dayOfWeek, $unserialized->dayOfWeek);
        self::assertEquals($this->entry->dayOfWeekAlias, $unserialized->dayOfWeekAlias);
        self::assertEquals($this->entry->dayOfYear, $unserialized->dayOfYear);
        self::assertEquals($this->entry->dayOfYearAlias, $unserialized->dayOfYearAlias);

        self::assertEquals($this->entry->time, $unserialized->time);
        self::assertEquals($this->entry->timeFormatted->resetMicro(), $unserialized->timeFormatted);
        self::assertEquals($this->entry->timeFormatted->format('H/i/s'), $serialized['timeFormatted']);
        self::assertEquals($this->entry->timeAlias, $unserialized->timeAlias);
        self::assertEquals($this->entry->timeAliasFormatted->resetMicro(), $unserialized->timeAliasFormatted);
        self::assertEquals($this->entry->timeAliasFormatted->format('H/i/s'), $serialized['timeAliasFormatted']);

        self::assertEquals($this->entry->hour, $unserialized->hour);
        self::assertEquals($this->entry->hourAlias, $unserialized->hourAlias);
        self::assertEquals($this->entry->minute, $unserialized->minute);
        self::assertEquals($this->entry->minuteAlias, $unserialized->minuteAlias);
        self::assertEquals($this->entry->second, $unserialized->second);
        self::assertEquals($this->entry->secondAlias, $unserialized->secondAlias);

        self::assertEquals($this->entry->durationInSeconds, $unserialized->durationInSeconds);
        self::assertEquals($this->entry->durationInHours->roundToHours(), $unserialized->durationInHours);
        self::assertEquals($this->entry->durationInHours->inHours(), $serialized['durationInHours']);
        self::assertEquals($this->entry->durationTimeInterval, $unserialized->durationTimeInterval);
        self::assertEquals(\sprintf(
            '%02d:%02d:%02d.%06d',
            $this->entry->durationTimeInterval->inHours(),
            $this->entry->durationTimeInterval->minutes(),
            $this->entry->durationTimeInterval->seconds(),
            $this->entry->durationTimeInterval->microseconds(),
        ), $serialized['durationTimeInterval']);
        self::assertEquals($this->entry->durationArray, $unserialized->durationArray);
        self::assertEquals([
            'days' => $this->entry->durationArray->days(),
            'hours' => $this->entry->durationArray->hours(),
            'minutes' => $this->entry->durationArray->minutes(),
            'seconds' => $this->entry->durationArray->seconds(),
            'micros' => $this->entry->durationArray->microseconds(),
        ], $serialized['durationArray']);
        self::assertEquals($this->entry->durationAlias, $unserialized->durationAlias);

        self::assertEquals($this->entry->instant, $unserialized->instant);
        self::assertEquals($this->entry->instantAlias, $unserialized->instantAlias);

        self::assertEquals($this->entry->localDateTime, $unserialized->localDateTime);
        self::assertEquals($this->entry->localDateTimeFormatted, $unserialized->localDateTimeFormatted);
        self::assertEquals(
            $this->entry->localDateTimeFormatted->format('F jS, Y, H:i:s'),
            $serialized['localDateTimeFormatted'],
        );
        self::assertEquals($this->entry->localDateTimeAlias, $unserialized->localDateTimeAlias);
        self::assertEquals($this->entry->localDateTimeAliasFormatted, $unserialized->localDateTimeAliasFormatted);
        self::assertEquals(
            $this->entry->localDateTimeAliasFormatted->format('F jS, Y, H:i'),
            $serialized['localDateTimeAliasFormatted'],
        );

        self::assertEquals($this->entry->zonedDateTime, $unserialized->zonedDateTime);
        self::assertEquals($this->entry->zonedDateTimeFormatted, $unserialized->zonedDateTimeFormatted);
        self::assertEquals(
            $this->entry->zonedDateTimeFormatted->format(\DateTimeInterface::RSS),
            $serialized['zonedDateTimeFormatted'],
        );
        self::assertEquals($this->entry->zonedDateTimeTs, $unserialized->zonedDateTimeTs);
        self::assertEquals((int)$this->entry->zonedDateTimeTs->format('U'), $serialized['zonedDateTimeTs']);
        self::assertEquals($this->entry->zonedDateTimeTsMicro, $unserialized->zonedDateTimeTsMicro);
        self::assertEquals($this->entry->zonedDateTimeTsMicro->format('U.u'), $serialized['zonedDateTimeTsMicro']);
        self::assertEquals($this->entry->zonedDateTimeAlias, $unserialized->zonedDateTimeAlias);
        self::assertEquals($this->entry->zonedDateTimeAliasFormatted->resetMicro(), $unserialized->zonedDateTimeAliasFormatted);
        self::assertEquals(
            $this->entry->zonedDateTimeAliasFormatted->format(\DateTimeInterface::RSS),
            $serialized['zonedDateTimeAliasFormatted'],
        );

        self::assertEquals($this->entry->nativeDateTime, $unserialized->nativeDateTime);
    }
}
