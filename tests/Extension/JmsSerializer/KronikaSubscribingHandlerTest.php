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
use PHPUnit\Framework\TestCase;

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
                (new JsonSerializationVisitorFactory())
                    ->setOptions(
                        JSON_UNESCAPED_UNICODE
                        | JSON_PRESERVE_ZERO_FRACTION
                        | JSON_PRETTY_PRINT,
                    ),
            )
            ->setSerializationContextFactory(fn() => (new SerializationContext())->setSerializeNull(true))
            ->addDefaultDeserializationVisitors()
            ->addDefaultHandlers()
            ->configureHandlers(function (HandlerRegistry $registry) {
                $registry->registerSubscribingHandler(new KronikaSubscribingHandler());
            })
            ->build()
        ;

        $this->entry = new Foo(
            date: Date::of(2025, 12, 31),
            year: Date\Year::of(2000),
            month1: Date\Month::January,
            month2: Date\Month::of(2),
            dayOfMonth: Date\DayOfMonth::of(25),
            dayOfWeek1: Date\DayOfWeek::Sunday,
            dayOfWeek2: Date\DayOfWeek::Monday,
            time: Time::of(12, 35, Time\Second::of(55, 999)),
            hour: Time\Hour::of(9),
            minute: Time\Minute::of(55),
            second: Time\Second::of(45, 6789),
            duration: Duration::of(days: 1, hours: 2, minutes: 25, seconds: 99),
            instant: Instant::of(123456789, 54321),
            localDateTime: LocalDateTime::of(Date::of(1985, 10, 31), Time::midnight()),
            zonedDateTime: ZonedDateTime::of(Date::of(1990, 9, 5), Time::midday(), new \DateTimeZone('+01:00')),
            nativeDateTime: new \DateTimeImmutable('2000-05-24 11:30:30'),
        );
    }

    public function testSerializeAndDeserialize(): void
    {
        $serialized = $this->serializer->toArray($this->entry);
        $unserialized = $this->serializer->fromArray($serialized, Foo::class);

        $this->assertSame($this->entry->date, $unserialized->date);
        $this->assertSame($this->entry->year, $unserialized->year);
        $this->assertSame($this->entry->month1, $unserialized->month1);
        $this->assertSame($this->entry->month2, $unserialized->month2);
        $this->assertSame($this->entry->dayOfMonth, $unserialized->dayOfMonth);
        $this->assertSame($this->entry->dayOfWeek1, $unserialized->dayOfWeek1);
        $this->assertSame($this->entry->dayOfWeek2, $unserialized->dayOfWeek2);

        $this->assertSame($this->entry->time, $unserialized->time);
        $this->assertSame($this->entry->hour, $unserialized->hour);
        $this->assertSame($this->entry->minute, $unserialized->minute);
        $this->assertSame($this->entry->second, $unserialized->second);

        $this->assertSame($this->entry->duration, $unserialized->duration);
        $this->assertSame($this->entry->instant, $unserialized->instant);
        $this->assertSame($this->entry->localDateTime, $unserialized->localDateTime);
        $this->assertSame($this->entry->zonedDateTime, $unserialized->zonedDateTime);

        $this->assertEquals($this->entry->nativeDateTime, $unserialized->nativeDateTime);
    }
}
