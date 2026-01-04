# Kronika
The library provides date-time value objects such as "Date", "Time", "LocalDateTime", etc.

## Installation
The recommended way to install Kronika is through
[Composer](https://getcomposer.org/).
```shell
composer require frostealth/kronika
```

## Version Guidance
| Version | Status |      Branch       | PHP Version |
|:-------:|:-------|:-----------------:|:-----------:|
|   0.3   | Dev    | [0.x][branch-0.x] |    ^8.4     |
|   0.2   | Latest |         -         |    ^8.4     |
|   0.1   | EOL    | [0.1][branch-0.1] | >=8.3,<=8.5 |

[branch-0.x]: https://github.com/frostealth/kronika/tree/0.x
[branch-0.1]: https://github.com/frostealth/kronika/tree/0.1

## Usage

- [Date](#date)
- [Time](#time)
- [LocalDateTime](#localdatetime)
- [ZonedDateTime](#zoneddatetime)
- [Clock](#clock)
- Range:
  - [TimeRange](#timerange)
  - [DateRange](#daterange)
  - [DateTimeRange](#datetimerange)
- Extensions:
  - [Doctrine][extension-doctrine]
  - [JMS Serializer][extension-jms-serializer]
  - [Laravel][extension-laravel]
  - [Symfony Serializer][extension-symfony]
  - [Yii2][extension-yii2]

[extension-doctrine]: src/Extension/Doctrine/README.md
[extension-jms-serializer]: src/Extension/JmsSerializer/README.md
[extension-laravel]: src/Extension/Laravel/README.md
[extension-symfony]: src/Extension/Symfony/README.md
[extension-yii2]: src/Extension/Yii2/README.md

### Date
`Kronika\Date` represents a date without specifying a time of day.

The following units of date are available:
- `Year`
- `Month`
- `DayOfMonth`
- `DayOfWeek`
- `DayOfYear`

```php
// creating the "Date" instance
$date = Date::of(year: 2025, month: 12, day:31);
// or from "\DateTimeInterface"
$date = Date::ofDateTime(new \DateTimeImmutable('2025-12-31'));
// or from a date string
$date = Date::parse('2025-12-31');

// formatting the "Date"
echo $date->format('Y-m-d');        // '2025-12-31'
echo $date->format('l, F jS, Y.');  // 'Wednesday, December 31st, 2025.'

$year      = $date->year();       // Year::of(2025)
$month     = $date->month();      // Month::of(12)
$day       = $date->day();        // DayOfMonth::of(31)
$dayOfWeek = $date->dayOfWeek();  // DayOfWeek::of(3)

// changing the year, month, day, weekday
$date = $date->with(Year::of(2026));
echo $date->format('l, F jS, Y.');  // 'Thursday, December 31st, 2026.'
echo $date->is(Year::of(2026));     // true

$date = $date->with(Month::January)->with(DayOfMonth::of(12));
echo $date->format('l, F jS, Y.');  // 'Monday, January 12th, 2026.'
echo $date->is(DayOfMonth::of(12)); // true

$date = $date->startOfMonth();
echo $date->format('l, F jS, Y.');  // 'Thursday, January 1st, 2026.'

// changing the day of week
$date = $date->with(DayOfWeek::Friday);
echo $date->format('l, F jS, Y.');  // 'Friday, January 2nd, 2026.'
echo $date->is(DayOfWeek::Friday);  // true

// adding an amount of days
$date = $date->add(Duration::of(days: 3));
echo $date->format('l, F jS, Y.');  // 'Monday, January 5th, 2026.'

// subtracting an amount of days
$date = $date->sub(Duration::of(hours: 48));
echo $date->format('l, F jS, Y.');  // 'Saturday, January 3rd, 2026.'

// getting the duration from one date to another one
$duration = $date->until(Date::of(year: 2026, month: 1, day: 5));
echo $duration->days();     // 2
echo $duration->hours();    // 0
echo $duration->minutes();  // 0
```

### Time
`Kronika\Time` represents a time of day without specifying a date.

The following units of time are available:
- `Hour`
- `Minute`
- `Second`

```php
// creating the "Time" instance
$time = Time::of(hour: 9, minutes: 10, seconds:30);
// or from "\DateTimeInterface"
$time = Time::ofDateTime(new \DateTimeImmutable('09:10:30'));
// or from time string
$time = Time::parse('09:10:30.000000');

// formatting the "Time"
echo $time->format('H:i:s');    // '09:10:30'
echo $time->format('H:i:s.u');  // '09:10:30.000000'

$hour   = $time->hour();    // Hour::of(9)
$minute = $time->minute();  // Minute::of(10)
$second = $time->second();  // Second::of(30)

// changing the hour, minute and second
$time = $time->with(Hour::of(12));
echo $time->format('H:i:s');  // '12:10:30'
echo $time->is(Hour::of(12)); // true

$time = $time->with(Minute::of(30))->with(Second::zero());
echo $time->format('H:i:s');  // '12:30:00'

// comparing time or its unit to another one
$other = $time->with(Second::of(0, micro: 999999));
echo $time->is($other);                     // false
echo $time->is($other, Precision::Second);  // true
echo $time->is(                             // true
    $other->with(Second::of(59)),
    Precision::Minute,
);

// adding an amount of hours, minutes, seconds
$time = $time->add(Duration::of(hours: 3, minutes: 30, seconds: 30));
echo $time->format('H:i:s');  // '16:00:30'

// subtracting an amount of hours, minutes, seconds
$time = $time->sub(Duration::of(hours: 2, minutes: 120, seconds: 30));
echo $time->format('H:i:s');  // '12:00:00'

// getting the duration from one time to another one
$duration = $time->until(Time::of(hour: 18, minute: 30, second: 30));
echo $duration->hours();      // 6
echo $duration->minutes();    // 30
echo $duration->second();     // 30
echo $duration->inMinutes();  // 390
```

### LocalDateTime
`Kronika\LocalDateTime` represents a local date-time without time-zone.

```php
// creating the "LocalDateTime" instance
$date     = Date::of(year: 2025, month: 12, day: 31);
$time     = Time::midday();
$datetime = LocalDateTime::of($date, $time);
// or
$datetime = $date->at($time);
// or from "\DateTimeInterface"
$datetime = LocalDateTime::ofDateTime(new \DateTimeImmutable('2025-12-31 12:00:00'));
// or from a date-time string
$datetime = LocalDateTime::parse('2025-12-31 12:00:00');

// formatting the "LocalDateTime"
echo $datetime->format('Y-m-d H:i:s');  // '2025-12-31 12:00:00'

$year   = $datetime->year();    // Year::of(2025)
$month  = $datetime->month();   // Month::of(12)
$day    = $datetime->day();     // DayOfMonth::of(31)
$hour   = $datetime->hour();    // Hour::of(12)
$minute = $datetime->minute();  // Minute::zero()
$second = $datetime->second();  // Second::zero()
$date   = $datetime->date();    // Date::of(2025, 12, 31)
$time   = $datetime->time();    // Time::of(12, 0, 0)

// changing the year, month, day, hour, minute and second is similar to "Date" and "Time"
$datetime = $datetime->with(Hour::of(18))->with(Minute::of(30));
echo $datetime->format('Y-m-d H:i:s');  // '2025-12-31 18:30:00'
echo $datetime->is(Hour::of(18));       // true

// adding and subtracting an amount of days, hours,
// minutes and seconds are similar to "Date" and "Time"
$datetime = $datetime->add(Duration::of(hours: 6, minutes: 30, seconds: 30));
echo $datetime->format('Y-m-d H:i:s');  // '2026-01-01 01:00:30'

$datetime = $datetime->sub(Duration::of(hours: 12, minutes: 60, seconds: 30));
echo $datetime->format('Y-m-d H:i:s');  // '2025-12-31 12:00:00'

// getting the duration from one "LocalDateTime" to another one
$duration = $datetime->until(
    LocalDateTime::of(Date::of(year: 2026, month: 1, day: 14), Time::midnight()),
);
echo $duration->days();     // 13
echo $duration->hours();    // 12
echo $duration->minutes();  // 0
echo $duration->second();   // 0
echo $duration->inHours();  // 324

// getting "\DateTimeImmutable" and "\DateTime"
$immutable = $datetime->toNative(new \DateTimeZone('UTC'));         // "\DateTimeImmutable"
$mutable   = $datetime->toNativeMutable(new \DateTimeZone('UTC'));  // "\DateTime"
```

### ZonedDateTime
`Kronika\ZonedDateTime` represents a date-time with time-zone.

The API of `Kronika\ZonedDateTime` is similar to `Kronika\LocalDateTime`.

`Kronika\ZonedDateTime` class extends `\DateTimeImmutable`.

```php
// creating the "ZonedDateTime" instance
$date     = Date::of(year: 2025, month: 12, day: 31);
$time     = Time::midday();
$timezone = new \DateTimeZone('UTC')
$datetime = ZonedDateTime::of($date, $time, $timezone);
// or
$datetime = $date->at($time)->at($timezone);
// or
$datetime = ZonedDateTime::ofLocal(LocalDateTime::of($date, $time), $timezone);
// or
$datetime = LocalDateTime::of($date, $time)->at($timezone);
// or
$datetime = ZonedDateTime::utcOf($date, $time);
// or with current time and specified time-zone
$datetime = now($timezone);
// or from "\DateTimeInterface"
$datetime = ZonedDateTime::ofDateTime(new \DateTimeImmutable('2025-12-31 12:00:00 UTC'));
// or from a date-time string with time-zone
$datetime = ZonedDateTime::parse('2025-12-31 12:00:00 UTC');
// or from a date-time string without time-zone
$datetime = ZonedDateTime::parse('2025-12-31 12:00:00', new \DateTimeZone('UTC'));

// formatting the "ZonedDateTime"
echo $datetime->format(\DateTimeInterface::ATOM);  // '2025-12-31T12:00:00+00:00'

$year      = $datetime->year();       // Year::of(2025)
$month     = $datetime->month();      // Month::of(12)
$day       = $datetime->day();        // DayOfMonth::of(31)
$hour      = $datetime->hour();       // Hour::of(12)
$minute    = $datetime->minute();     // Minute::zero()
$second    = $datetime->second();     // Second::zero()
$date      = $datetime->date();       // Date::of(2025, 12, 31)
$time      = $datetime->time();       // Time::of(12, 0, 0)
$timezone  = $datetime->timezone();   // \DateTimeZone('UTC')
$timestamp = $datetime->timestamp();  // float(1767182400.001234)

// changing the year, month, day, hour, minute and second is similar to "LocalDateTime"
$datetime = $datetime->with(Hour::of(18))->with(Minute::of(30));
echo $datetime->format(\DateTimeInterface::ATOM);  // '2025-12-31T18:30:00+00:00'
echo $datetime->is(Hour::of(18));                  // true

// changing the time-zone doesn't shift the time,
// to shift the time use "shift()" method
echo $datetime->with(new \DateTimeZone('+01:00'))
              ->format(\DateTimeInterface::ATOM);  // '2025-12-31T18:30:00+01:00

// adding and subtracting an amount of days, hours, minutes
// and seconds are similar to "LocalDateTime"
$datetime = $datetime->add(Duration::of(hours: 6, minutes: 30, seconds: 30));
echo $datetime->format(\DateTimeInterface::ATOM);  // '2026-01-01T01:00:30+00:00'

$datetime = $datetime->sub(Duration::of(hours: 12, minutes: 60, seconds: 30));
echo $datetime->format(\DateTimeInterface::ATOM);  // '2025-12-31T12:00:00+00:00'

// shifting the timezone
$datetime = $datetime->shift(new \DateTimeZone('+01:00'));
echo $datetime->format(\DateTimeInterface::ATOM);  // '2025-12-31T13:00:00+01:00'

// getting the duration from one "ZonedDateTime" to another one
$duration = $datetime->until(new \DateTime('2026-01-14T12:30:15+00:00'));
$days     = $duration->days();     // 14
$hours    = $duration->hours();    // 0
$minutes  = $duration->minutes();  // 30
$seconds  = $duration->second();   // 15
$inHours  = $duration->inHours();  // 336

// getting "\DateTimeImmutable" and "\DateTime"
$immutable = $datetime->toNative();         // "\DateTimeImmutable"
$mutable   = $datetime->toNativeMutable();  // "\DateTime"
```

### Clock
`Kronika\Clock` decouples your code from the system clock
and has the following implementations:
- `SystemClock` returns the current time, this is the same as doing `new \DateTime()`.
- `InaccurateClock` ignores a second or microsecond of the current time.
- `PsrClock` implements [PSR-20: Clock](https://www.php-fig.org/psr/psr-20/).
- `FrozenClock` doesn't move forward on its own, useful in tests.
- `MutableClock` allows to manipulate with clock, useful in tests.

### TimeRange
`Kronika\Range\TimeRange` represents a range between two moments of day.

```php
// creating "TimeRange"
$range = TimeRange::of(
    from: Time::midday(),   // inclusive
    to: Time::of(13, 30),   // exclusive
);
echo $range->from()->format('H:i:s');  // 12:00:00
echo $range->to()->format('H:i:s');    // 13:30:00

echo $range->contains(Time::midday());    // true
echo $range->contains(Time::of(13, 30));  // false

// getting each item of this range with the specified step
foreach ($range->each(Duration::of(minutes: 30)) as $item) {
    echo $item->format('H:i:s');
}
// 12:00:00
// 12:30:00
// 13:00:00

// splitting the range into smaller ranges
foreach ($range->split(Duration::ofHour()) as $item) {
    echo $item->from() . ' - ' . $item->to();
}
// 12:00:00 - 13:00:00
// 13:00:00 - 13:30:00

// getting an intersection between ranges
$foo = TimeRange::of(Time::of(13, 0), Time::of(14, 0));
if ($range->overlaps($foo)) {
    $bar = $range->intersection($foo);  // 13:00:00 - 13:30:00
}

// getting a gap between ranges
$foo = TimeRange::of(Time::of(14, 0), Time::of(15, 0));
if (! $range->overlaps($foo)) {
    $bar = $range->gap($foo);  // 13:30:00 - 14:00:00
}
```

### DateRange
`Kronika\Range\DateRange` represents a range between two dates.

```php
// creating "DateRange"
$range = DateRange::of(
    from: Date::of(2025, 12, 15),  // inclusive
    to: Date::of(2025, 12, 18),    // exclusive
);
echo $range->from()->format('Y-m-d');  // 2025-12-15
echo $range->to()->format('Y-m-d');    // 2025-12-18

echo $range->contains(Date::of(2025, 12, 15));  // true
echo $range->contains(Date::of(2025, 12, 18));  // false

// getting each item of this range with the specified step
// minimal step is 1 day
foreach ($range->each(Duration::of(days: 2)) as $item) {
    echo $item->format('Y-m-d');
}
// 2025-12-15
// 2025-12-17

// splitting the range into smaller ranges
foreach ($range->split(Duration::of(days: 2)) as $item) {
    echo $item->from() . ' - ' . $item->to();
}
// 2025-12-15 - 2025-12-17
// 2025-12-17 - 2025-12-18

// getting an intersection between ranges
$foo = DateRange::of(Date::of(2025, 12, 17), Date::of(2025, 12, 20));
if ($range->overlaps($foo)) {
    $bar = $range->intersection($foo);  // 2025-12-17 - 2025-12-18
}

// getting a gap between ranges
$foo = DateRange::of(Date::of(2025, 12, 20), Date::of(2025, 12, 22));
if (! $range->overlaps($foo)) {
    $bar = $range->gap($foo);  // 2025-12-18 - 2025-12-20
}
```

### DateTimeRange
- `Kronika\Range\LocalDateTimeRange` represents a range between two instances of `LocalDateTime`.
- `Kronika\Range\ZonedDateTimeRange` represents a range between two instances of `ZonedDateTime`.

```php
// creating "ZonedDateTimeRange"
$range = ZonedDateTimeRange::of(
    from: ZonedDateTime::parse('2025-12-15 12:30:45 +01:00'),  // inclusive
    to: ZonedDateTime::parse('2025-12-18 10:00:30 +01:00'),    // exclusive
);
echo $range->from()->format('Y-m-d H:i:s P');  // 2025-12-15 12:30:45 +01:00
echo $range->to()->format('Y-m-d H:i:s P');    // 2025-12-18 10:00:30 +01:00

echo $range->contains(ZonedDateTime::parse('2025-12-15 12:30:45 +01:00'));  // true
echo $range->contains(ZonedDateTime::parse('2025-12-18 10:00:30 +01:00'));  // false

// getting each item of this range with the specified step
// the type of each item will be the same as "since"
foreach ($range->each(Duration::ofDay()) as $item) {
    echo $item->format('Y-m-d H:i:s P');
}
// 2025-12-15 12:30:45 +01:00
// 2025-12-16 12:30:45 +01:00
// 2025-12-17 12:30:45 +01:00

// splitting the range into smaller ranges
foreach ($range->split(Duration::ofDay()) as $item) {
    echo $item->from() . ' - ' . $item->to();
}
// 2025-12-15 12:30:45 +01:00 - 2025-12-16 12:30:45 +01:00
// 2025-12-16 12:30:45 +01:00 - 2025-12-17 12:30:45 +01:00
// 2025-12-17 12:30:45 +01:00 - 2025-12-18 10:00:30 +01:00

// getting an intersection between ranges
$foo = ZonedDateTimeRange::of(
    ZonedDateTime::parse('2025-12-17 20:15:10 +01:00'),
    ZonedDateTime::parse('2025-12-20 12:00:00 +01:00'),
);
if ($range->overlaps($foo)) {
    $bar = $range->intersection($foo);
    // 2025-12-17 20:15:10 +01:00 - 2025-12-18 10:00:30 +01:00
}

// getting a gap between ranges
$foo = ZonedDateTimeRange::of(
    ZonedDateTime::parse('2025-12-20 12:00:00 +01:00'),
    ZonedDateTime::parse('2025-12-12 10:00:00 +01:00'),
);
if (! $range->overlaps($foo)) {
    $bar = $range->gap($foo);
    // 2025-12-18 10:00:30 +01:00 - 2025-12-20 12:00:00 +01:00
}
```
