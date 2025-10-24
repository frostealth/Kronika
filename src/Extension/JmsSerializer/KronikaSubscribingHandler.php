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

namespace Kronika\Extension\JmsSerializer;

use JMS\Serializer\GraphNavigatorInterface;
use JMS\Serializer\Handler\SubscribingHandlerInterface;
use JMS\Serializer\Visitor\DeserializationVisitorInterface;
use JMS\Serializer\Visitor\SerializationVisitorInterface;
use Kronika\Date;
use Kronika\Duration;
use Kronika\Instant;
use Kronika\LocalDateTime;
use Kronika\Time;
use Kronika\Time\Second;
use Kronika\ZonedDateTime;

final readonly class KronikaSubscribingHandler implements SubscribingHandlerInterface
{
    #[\Override]
    public static function getSubscribingMethods(): array
    {
        $methods = [];
        $formats = ['json', 'xml', 'yml'];
        foreach ($formats as $format) {
            // Date
            $methods[] = [
                'type' => Date::class,
                'method' => 'serializeDate',
                'direction' => GraphNavigatorInterface::DIRECTION_SERIALIZATION,
                'format' => $format,
            ];
            $methods[] = [
                'type' => Date::class,
                'method' => 'deserializeDate',
                'direction' => GraphNavigatorInterface::DIRECTION_DESERIALIZATION,
                'format' => $format,
            ];
            $methods[] = [
                'type' => Date\Year::class,
                'method' => 'serializeYear',
                'direction' => GraphNavigatorInterface::DIRECTION_SERIALIZATION,
                'format' => $format,
            ];
            $methods[] = [
                'type' => Date\Year::class,
                'method' => 'deserializeYear',
                'direction' => GraphNavigatorInterface::DIRECTION_DESERIALIZATION,
                'format' => $format,
            ];
            $methods[] = [
                'type' => 'KronikaMonth',
                'method' => 'serializeMonth',
                'direction' => GraphNavigatorInterface::DIRECTION_SERIALIZATION,
                'format' => $format,
            ];
            $methods[] = [
                'type' => 'KronikaMonth',
                'method' => 'deserializeMonth',
                'direction' => GraphNavigatorInterface::DIRECTION_DESERIALIZATION,
                'format' => $format,
            ];
            $methods[] = [
                'type' => 'KronikaDayOfWeek',
                'method' => 'serializeDayOfWeek',
                'direction' => GraphNavigatorInterface::DIRECTION_SERIALIZATION,
                'format' => $format,
            ];
            $methods[] = [
                'type' => 'KronikaDayOfWeek',
                'method' => 'deserializeDayOfWeek',
                'direction' => GraphNavigatorInterface::DIRECTION_DESERIALIZATION,
                'format' => $format,
            ];
            $methods[] = [
                'type' => Date\DayOfMonth::class,
                'method' => 'serializeDayOfMonth',
                'direction' => GraphNavigatorInterface::DIRECTION_SERIALIZATION,
                'format' => $format,
            ];
            $methods[] = [
                'type' => Date\DayOfMonth::class,
                'method' => 'deserializeDayOfMonth',
                'direction' => GraphNavigatorInterface::DIRECTION_DESERIALIZATION,
                'format' => $format,
            ];

            // Time
            $methods[] = [
                'type' => Time::class,
                'method' => 'serializeTime',
                'direction' => GraphNavigatorInterface::DIRECTION_SERIALIZATION,
                'format' => $format,
            ];
            $methods[] = [
                'type' => Time::class,
                'method' => 'deserializeTime',
                'direction' => GraphNavigatorInterface::DIRECTION_DESERIALIZATION,
                'format' => $format,
            ];
            $methods[] = [
                'type' => Time\Hour::class,
                'method' => 'serializeHour',
                'direction' => GraphNavigatorInterface::DIRECTION_SERIALIZATION,
                'format' => $format,
            ];
            $methods[] = [
                'type' => Time\Hour::class,
                'method' => 'deserializeHour',
                'direction' => GraphNavigatorInterface::DIRECTION_DESERIALIZATION,
                'format' => $format,
            ];
            $methods[] = [
                'type' => Time\Minute::class,
                'method' => 'serializeMinute',
                'direction' => GraphNavigatorInterface::DIRECTION_SERIALIZATION,
                'format' => $format,
            ];
            $methods[] = [
                'type' => Time\Minute::class,
                'method' => 'deserializeMinute',
                'direction' => GraphNavigatorInterface::DIRECTION_DESERIALIZATION,
                'format' => $format,
            ];
            $methods[] = [
                'type' => Time\Second::class,
                'method' => 'serializeSecond',
                'direction' => GraphNavigatorInterface::DIRECTION_SERIALIZATION,
                'format' => $format,
            ];
            $methods[] = [
                'type' => Time\Second::class,
                'method' => 'deserializeSecond',
                'direction' => GraphNavigatorInterface::DIRECTION_DESERIALIZATION,
                'format' => $format,
            ];

            // General
            $methods[] = [
                'type' => Duration::class,
                'method' => 'serializeDuration',
                'direction' => GraphNavigatorInterface::DIRECTION_SERIALIZATION,
                'format' => $format,
            ];
            $methods[] = [
                'type' => Duration::class,
                'method' => 'deserializeDuration',
                'direction' => GraphNavigatorInterface::DIRECTION_DESERIALIZATION,
                'format' => $format,
            ];
            $methods[] = [
                'type' => Instant::class,
                'method' => 'serializeInstant',
                'direction' => GraphNavigatorInterface::DIRECTION_SERIALIZATION,
                'format' => $format,
            ];
            $methods[] = [
                'type' => Instant::class,
                'method' => 'deserializeInstant',
                'direction' => GraphNavigatorInterface::DIRECTION_DESERIALIZATION,
                'format' => $format,
            ];
            $methods[] = [
                'type' => LocalDateTime::class,
                'method' => 'serializeLocalDateTime',
                'direction' => GraphNavigatorInterface::DIRECTION_SERIALIZATION,
                'format' => $format,
            ];
            $methods[] = [
                'type' => LocalDateTime::class,
                'method' => 'deserializeLocalDateTime',
                'direction' => GraphNavigatorInterface::DIRECTION_DESERIALIZATION,
                'format' => $format,
            ];
            $methods[] = [
                'type' => ZonedDateTime::class,
                'method' => 'serializeZonedDateTime',
                'direction' => GraphNavigatorInterface::DIRECTION_SERIALIZATION,
                'format' => $format,
            ];
            $methods[] = [
                'type' => ZonedDateTime::class,
                'method' => 'deserializeZonedDateTime',
                'direction' => GraphNavigatorInterface::DIRECTION_DESERIALIZATION,
                'format' => $format,
            ];
        }

        return $methods;
    }

    public function serializeDate(SerializationVisitorInterface $visitor, ?Date $date, array $type): ?string
    {
        if ($date === null) {
            return $visitor->visitNull($date, $type);
        }

        return $visitor->visitString(\sprintf(
            '%04d-%02d-%02d',
            $date->year()->number(),
            $date->month()->number(),
            $date->day()->number(),
        ), $type);
    }

    public function deserializeDate(DeserializationVisitorInterface $visitor, ?string $value, array $type): ?Date
    {
        $value = $visitor->visitString($value, $type);
        if ($value === null) {
            return $visitor->visitNull($value, $type);
        }

        [$year, $month, $day] = \sscanf($value, '%4d-%2d-%2d');

        return Date::of(year: (int) $year, month: (int) $month, day: (int) $day);
    }

    public function serializeYear(SerializationVisitorInterface $visitor, ?Date\Year $year, array $type): ?int
    {
        if ($year === null) {
            return $visitor->visitNull($year, $type);
        }

        return $visitor->visitInteger($year->number(), $type);
    }

    public function deserializeYear(DeserializationVisitorInterface $visitor, ?int $value, array $type): ?Date\Year
    {
        $value = $visitor->visitInteger($value, $type);
        if ($value === null) {
            return $visitor->visitNull($value, $type);
        }

        return Date\Year::of($value);
    }

    public function serializeMonth(SerializationVisitorInterface $visitor, ?Date\Month $month, array $type): ?int
    {
        if ($month === null) {
            return $visitor->visitNull($month, $type);
        }

        return $visitor->visitInteger($month->number(), $type);
    }

    public function deserializeMonth(DeserializationVisitorInterface $visitor, ?int $value, array $type): ?Date\Month
    {
        $value = $visitor->visitInteger($value, $type);
        if ($value === null) {
            return $visitor->visitNull($value, $type);
        }

        return Date\Month::of($value);
    }

    public function serializeDayOfWeek(SerializationVisitorInterface $visitor, ?Date\DayOfWeek $dayOfWeek, array $type): ?int
    {
        if ($dayOfWeek === null) {
            return $visitor->visitNull($dayOfWeek, $type);
        }

        return $visitor->visitInteger($dayOfWeek->number(), $type);
    }

    public function deserializeDayOfWeek(DeserializationVisitorInterface $visitor, ?int $value, array $type): ?Date\DayOfWeek
    {
        $value = $visitor->visitInteger($value, $type);
        if ($value === null) {
            return $visitor->visitNull($value, $type);
        }

        return Date\DayOfWeek::of($value);
    }

    public function serializeDayOfMonth(SerializationVisitorInterface $visitor, ?Date\DayOfMonth $dayOfMonth, array $type): ?int
    {
        if ($dayOfMonth === null) {
            return $visitor->visitNull($dayOfMonth, $type);
        }

        return $visitor->visitInteger($dayOfMonth->number(), $type);
    }

    public function deserializeDayOfMonth(DeserializationVisitorInterface $visitor, ?int $value, array $type): ?Date\DayOfMonth
    {
        $value = $visitor->visitInteger($value, $type);
        if ($value === null) {
            return $visitor->visitNull($value, $type);
        }

        return Date\DayOfMonth::of($value);
    }

    public function serializeTime(SerializationVisitorInterface $visitor, ?Time $time, array $type): ?string
    {
        if ($time === null) {
            return $visitor->visitNull($time, $type);
        }

        return $visitor->visitString(\sprintf(
            '%02d:%02d:%02d.%06d',
            $time->hour()->value(),
            $time->minute()->value(),
            $time->second()->second(),
            $time->second()->microsecond(),
        ), $type);
    }

    public function deserializeTime(DeserializationVisitorInterface $visitor, ?string $value, array $type): ?Time
    {
        $value = $visitor->visitString($value, $type);
        if ($value === null) {
            return $visitor->visitNull($value, $type);
        }

        [$hour, $minute, $second, $micro] = \sscanf($value, '%2d:%2d:%2d.%6d');

        return Time::of(
            hour: (int) $hour,
            minute: (int) $minute,
            second: Second::of(second: (int) $second, micro: (int) $micro),
        );
    }

    public function serializeHour(SerializationVisitorInterface $visitor, ?Time\Hour $hour, array $type): ?int
    {
        if ($hour === null) {
            return $visitor->visitNull($hour, $type);
        }

        return $visitor->visitInteger($hour->value(), $type);
    }

    public function deserializeHour(DeserializationVisitorInterface $visitor, ?int $value, array $type): ?Time\Hour
    {
        $value = $visitor->visitInteger($value, $type);
        if ($value === null) {
            return $visitor->visitNull($value, $type);
        }

        return Time\Hour::of($value);
    }

    public function serializeMinute(SerializationVisitorInterface $visitor, ?Time\Minute $minute, array $type): ?int
    {
        if ($minute === null) {
            return $visitor->visitNull($minute, $type);
        }

        return $visitor->visitInteger($minute->value(), $type);
    }

    public function deserializeMinute(DeserializationVisitorInterface $visitor, ?int $value, array $type): ?Time\Minute
    {
        $value = $visitor->visitInteger($value, $type);
        if ($value === null) {
            return $visitor->visitNull($value, $type);
        }

        return Time\Minute::of($value);
    }

    public function serializeSecond(SerializationVisitorInterface $visitor, ?Time\Second $second, array $type): ?string
    {
        if ($second === null) {
            return $visitor->visitNull($second, $type);
        }

        return $visitor->visitString(\sprintf('%02d.%06d', $second->second(), $second->microsecond()), $type);
    }

    public function deserializeSecond(DeserializationVisitorInterface $visitor, ?string $value, array $type): ?Time\Second
    {
        $value = $visitor->visitString($value, $type);
        if ($value === null) {
            return $visitor->visitNull($value, $type);
        }

        [$second, $micro] = \sscanf($value, '%2d.%6d');

        return Time\Second::of(second: (int) $second, micro: (int) $micro);
    }

    public function serializeDuration(SerializationVisitorInterface $visitor, ?Duration $duration, array $type): ?int
    {
        if ($duration === null) {
            return $visitor->visitNull($duration, $type);
        }

        return $visitor->visitInteger($duration->inSeconds(), $type);
    }

    public function deserializeDuration(DeserializationVisitorInterface $visitor, ?int $value, array $type): ?Duration
    {
        $value = $visitor->visitInteger($value, $type);
        if ($value === null) {
            return $visitor->visitNull($value, $type);
        }

        return Duration::of(seconds: $value);
    }

    public function serializeInstant(SerializationVisitorInterface $visitor, ?Instant $instant, array $type): ?string
    {
        if ($instant === null) {
            return $visitor->visitNull($instant, $type);
        }

        return $visitor->visitString(\sprintf('%d.%06d', $instant->second(), $instant->microsecond()), $type);
    }

    public function deserializeInstant(DeserializationVisitorInterface $visitor, ?string $value, array $type): ?Instant
    {
        $value = $visitor->visitString($value, $type);
        if ($value === null) {
            return $visitor->visitNull($value, $type);
        }

        [$second, $micro] = \sscanf($value, '%d.%6d');

        return Instant::of(second: (int) $second, micro: (int) $micro);
    }

    public function serializeLocalDateTime(SerializationVisitorInterface $visitor, ?LocalDateTime $datetime, array $type): ?string
    {
        if ($datetime === null) {
            return $visitor->visitNull($datetime, $type);
        }

        return $visitor->visitString(\sprintf(
            '%04d-%02d-%02dT%02d:%02d:%02d.%06d',
            $datetime->year()->number(),
            $datetime->month()->number(),
            $datetime->day()->number(),
            $datetime->hour()->value(),
            $datetime->minute()->value(),
            $datetime->second()->second(),
            $datetime->second()->microsecond(),
        ), $type);
    }

    public function deserializeLocalDateTime(DeserializationVisitorInterface $visitor, ?string $value, array $type): ?LocalDateTime
    {
        $value = $visitor->visitString($value, $type);
        if ($value === null) {
            return $visitor->visitNull($value, $type);
        }

        [$year, $month, $day, $hour, $minute, $second, $micro] = \sscanf($value, '%4d-%2d-%2dT%2d:%2d:%2d.%6d');

        return LocalDateTime::of(
            date: Date::of(year: (int) $year, month: (int) $month, day: (int) $day),
            time: Time::of(hour: (int) $hour, minute: (int) $minute, second: Second::of(second: (int) $second, micro: (int) $micro)),
        );
    }

    public function serializeZonedDateTime(SerializationVisitorInterface $visitor, ?ZonedDateTime $datetime, array $type): ?string
    {
        if ($datetime === null) {
            return $visitor->visitNull($datetime, $type);
        }

        return $visitor->visitString($datetime->format(\DateTimeInterface::ATOM), $type);
    }

    public function deserializeZonedDateTime(DeserializationVisitorInterface $visitor, ?string $value, array $type): ?ZonedDateTime
    {
        $value = $visitor->visitString($value, $type);
        if ($value === null) {
            return $visitor->visitNull($value, $type);
        }

        return ZonedDateTime::ofFormat(\DateTimeInterface::ATOM, $value);
    }
}
