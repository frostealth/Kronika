# Kronika

The library provides date-time value objects such as "Date", "Time", "LocalDateTime", etc.

## Usage

### Date

"Kronika\Date" represents a date without specifying a time.

```php
// creating the "Date" instance
$date = Date::of(year: 2025, month: 12, day:31);
// or using "\DateTimeInterface"
$date = Date::ofDateTime(new \DateTimeImmutable('2025-12-31'));

// formatting the "Date"
echo $date->format('Y-m-d');  // '2025-12-31'
echo $date->format('l, F jS, Y.');  // 'Wednesday, December 31st, 2025.'

$year = $date->year();  // Year::of(2025)
$month = $date->month();  // Month::of(12)
$day = $date->day();  // DayOfMonth::of(31)
$dayOfWeek = $date->dayOfWeek();  // DayOfWeek::of(3)

// changing the year, month, day, weekday
$date = $date->with(Year::of(2026));
echo $date->format('l, F jS, Y.');  // 'Thursday, December 31st, 2026.'

$date = $date->with(Month::January)->with(DayOfMonth::of(12));
echo $date->format('l, F jS, Y.');  // 'Monday, January 12th, 2026.'

$date = $date->toStartOfMonth();
echo $date->format('l, F jS, Y.');  // 'Thursday, January 1st, 2026.'

// changing the day of week
$date = $date->with(DayOfWeek::Friday);
echo $date->format('l, F jS, Y.');  // 'Friday, January 2nd, 2026.'

// adding an amount of days
$date = $date->add(Duration::of(days: 3));
echo $date->format('l, F jS, Y.');  // 'Monday, January 5th, 2026.'

// subtracting an amount of days
$date = $date->sub(Duration::of(hours: 48));
echo $date->format('l, F jS, Y.');  // 'Saturday, January 3rd, 2026.'

// getting the duration from one date to another
$duration = $date->until(Date::of(year: 2026, month: 1, day: 5));
echo $duration->days();  // 2
echo $duration->hours();  // 0
echo $duration->minutes();  // 0
```

### Time

"Kronika\Time" represents a time without specifying a date.

```php
// creating the "Time" instance
$time = Time::of(hour: 9, minutes: 10, seconds:30);
// or using "\DateTimeInterface"
$time = Time::ofDateTime(new \DateTimeImmutable('09:10:30'));

// formatting the "Time"
echo $time->format('H:i:s');  // '09:10:30'

$hour = $time->hour();  // Hour::of(9)
$minute = $time->minute();  // Minute::of(10)
$second = $time->second();  // Second::of(30)

// changing the hour, minute and second
$time = $time->with(Hour::of(12));
echo $time->format('H:i:s');  // '12:10:30'

$time = $time->with(Minute::of(30))->with(Second::zero());
echo $time->format('H:i:s');  // '12:30:00'

// adding an amount of hours, minutes, seconds
$time = $time->add(Duration::of(hours: 3, minutes: 30, seconds: 30));
echo $time->format('H:i:s');  // '16:00:30'

// subtracting an amount of hours, minutes, seconds
$time = $time->sub(Duration::of(hours: 2, minutes: 120, seconds: 30));
echo $time->format('H:i:s');  // '12:00:00'

// getting the duration from one time to another
$duration = $time->until(Time::of(hour: 18, minute: 30, second: 30));
echo $duration->hours();  // 6
echo $duration->minutes();  // 30
echo $duration->second();  // 30
echo $duration->inMinutes();  // 390
```

### LocalDateTime

"Kronika\LocalDateTime" represents a local date-time without a time-zone.

```php
// creating the "LocalDateTime" instance
$date = Date::of(year: 2025, month: 12, day: 31);
$time = Time::noon();
$dateTime = LocalDateTime::of($date, $time);
// or
$dateTime = $date->at($time);
// or using "\DateTimeInterface"
$dateTime = LocalDateTime::ofDateTime(new \DateTimeImmutable('2025-12-31 12:00:00'));

// formatting the "LocalDateTime"
echo $dateTime->format('Y-m-d H:i:s');  // '2025-12-31 12:00:00'

$year = $dateTime->year();  // Year::of(2025)
$month = $dateTime->month();  // Month::of(12)
$day = $dateTime->day();  // DayOfMonth::of(31)
$hour = $dateTime->hour();  // Hour::of(12)
$minute = $dateTime->minute();  // Minute::zero()
$second = $dateTime->second();  // Second::zero()
$date = $dateTime->date();  // Date::of(2025, 12, 31)
$time = $dateTime->time();  // Time::of(12, 0, 0)

// changing the year, month, day, hour, minute and second is similar to "Date" and "Time"
$dateTime = $dateTime->with(Hour::of(18))->with(Minute::of(30));
echo $dateTime->format('Y-m-d H:i:s');  // '2025-12-31 18:30:00'

// adding and subtracting an amount of days, hours, minutes and seconds are similar to "Date" and "Time"
$dateTime = $dateTime->add(Duration::of(hours: 6, minutes: 30, seconds: 30));
echo $dateTime->format('Y-m-d H:i:s');  // '2026-01-01 01:00:30'

$dateTime = $dateTime->sub(Duration::of(hours: 12, minutes: 60, seconds: 30));
echo $dateTime->format('Y-m-d H:i:s');  // '2025-12-31 12:00:00'

// getting the duration from one "LocalDateTime" to another
$duration = $dateTime->until(LocalDateTime::midnightOf(Date::of(year: 2026, month: 1, day: 14)));
echo $duration->days();  // 13
echo $duration->hours();  // 12
echo $duration->minutes();  // 0
echo $duration->second();  // 0
echo $duration->inHours();  // 324

// getting the "\DateTimeImmutable" and "\DateTime"
$immutable = $dateTime->toNative(new \DateTimeZone('UTC'));  // "\DateTimeImmutable"
$mutable = $dateTime->toNativeMutable(new \DateTimeZone('UTC'));  // "\DateTime"
```

### ZonedDateTime

"Kronika\ZonedDateTime" represents a date-time with a time-zone.
This class extends the native "\DateTimeImmutable".
The API of "Kronika\ZonedDateTime" is similar to "Kronika\LocalDateTime".

```php
// creating the "ZonedDateTime" instance
$date = Date::of(year: 2025, month: 12, day: 31);
$time = Time::noon();
$timezone = new \DateTimeZone('UTC')
$dateTime = ZonedDateTime::of($date, $time, $timezone);
// or
$dateTime = $date->at($time)->atTimezone($timezone);
// or
$dateTime = ZonedDateTime::ofLocal(LocalDateTime::of($date, $time), $timezone);
// or
$dateTime = ZonedDateTime::utcOf($date, $time);
// or using "\DateTimeInterface"
$dateTime = ZonedDateTime::ofDateTime(new \DateTimeImmutable('2025-12-31 12:00:00 UTC'));

// formatting the "ZonedDateTime"
echo $dateTime->format(\DateTimeInterface::ATOM);  // '2025-12-31T12:00:00+00:00'

$year = $dateTime->year();  // Year::of(2025)
$month = $dateTime->month();  // Month::of(12)
$day = $dateTime->day();  // DayOfMonth::of(31)
$hour = $dateTime->hour();  // Hour::of(12)
$minute = $dateTime->minute();  // Minute::zero()
$second = $dateTime->second();  // Second::zero()
$date = $dateTime->date();  // Date::of(2025, 12, 31)
$time = $dateTime->time();  // Time::of(12, 0, 0)
$timezone = $dateTime->timezone();  // \DateTimeZone('UTC')
$timestamp = $dateTime->timestamp();  // int(1767182400)

// changing the year, month, day, hour, minute and second is similar to "LocalDateTime"
$dateTime = $dateTime->with(Hour::of(18))->with(Minute::of(30));
echo $dateTime->format(\DateTimeInterface::ATOM);  // '2025-12-31T18:30:00+00:00'

// adding and subtracting an amount of days, hours, minutes and seconds are similar to "LocalDateTime"
$dateTime = $dateTime->add(Duration::of(hours: 6, minutes: 30, seconds: 30));
echo $dateTime->format(\DateTimeInterface::ATOM);  // '2026-01-01T01:00:30+00:00'

$dateTime = $dateTime->sub(Duration::of(hours: 12, minutes: 60, seconds: 30));
echo $dateTime->format(\DateTimeInterface::ATOM);  // '2025-12-31T12:00:00+00:00'

// shifting the timezone
$dateTime = $dateTime->shiftTimezone(new \DateTimeZone('+01:00'));
echo $dateTime->format(\DateTimeInterface::ATOM);  // '2025-12-31T13:00:00+01:00'

// getting the duration from one "ZonedDateTime" to another
$duration = $dateTime->until(new \DateTime('2026-01-14T12:30:15+00:00'));
$days = $duration->days();  // 14
$hours = $duration->hours();  // 0
$minutes = $duration->minutes();  // 30
$seconds = $duration->second();  // 15
$inHours = $duration->inHours();  // 336

// getting the "\DateTimeImmutable" and "\DateTime"
$immutable = $dateTime->toNative();  // "\DateTimeImmutable"
$mutable = $dateTime->toNativeMutable();  // "\DateTime"
```
