# Changelog
All notable changes to `Kronika` will be documented in this file.

## [0.2.4](https://github.com/frostealth/kronika/releases/tag/0.2.4) – 2025-11-12
### Added
- `Instant::resetMicro()` – resets microsecond.
- `Instant::resetSecond()` – resets second with microsecond.
- `Instant::resetMinute()` – resets minute, second and microsecond.
- `Instant::is()` and `Instant::isNot()` methods.
- `precision` option to comparison methods of `Instant`.
- `Date::is()` and `Date::isNot()` methods.
- `Year::is()` and `Year::isNot()` methods.
- `DayOfYear::is()` and `DayOfYear::isNot()` methods.
- `Month::is()` and `Month::isNot()` methods.
- `DayOfWeek::is()` and `DayOfWeek::isNot()` methods.
- `DayOfMonth::is()` and `DayOfMonth::isNot()` methods.
- `Time::is()` and `Time::isNot()` methods.
- `Hour::is()` and `Hour::isNot()` methods.
- `Minute::is()` and `Minute::isNot()` methods.
- `Second::is()` and `Second::isNot()` methods.
- `DateTime::is()` and `DateTime::isNot()` methods.
- `Duration::is()` and `Duration::isNot()` methods.
- `LocalDateTime::is()` and `LocalDateTime::isNot()` methods.
- `ZonedDateTime::is()` and `ZonedDateTime::isNot()` methods.
- `precision` option to `Instant::until()` and `Instant::difference()`.
- `precision` option to `DateTime::until()` and `DateTime::difference()`.
- `precision` option to `Time::until()` and `Time::difference()`.
- `Duration::ofMinute()` and `Duration::ofSecond()` methods.

### Deprecated
- `Date::isEqualTo()` and `Date::isNotEqualTo()` methods.
- `Year::isEqualTo()` and `Year::isNotEqualTo()` methods.
- `DayOfYear::isEqualTo()` and `DayOfYear::isNotEqualTo()` methods.
- `Month::isEqualTo()` and `Month::isNotEqualTo()` methods.
- `DayOfWeek::isEqualTo()` and `DayOfWeek::isNotEqualTo()` methods.
- `DayOfMonth::isEqualTo()` and `DayOfMonth::isNotEqualTo()` methods.
- `Time::isEqualTo()` and `Time::isNotEqualTo()` methods.
- `Hour::isEqualTo()` and `Hour::isNotEqualTo()` methods.
- `Minute::isEqualTo()` and `Minute::isNotEqualTo()` methods.
- `Second::isEqualTo()` and `Second::isNotEqualTo()` methods.
- `DateTime::isEqualTo()` and `DateTime::isNotEqualTo()` methods.
- `Duration::isEqualTo()` and `Duration::isNotEqualTo()` methods.
- `Instant::isEqualTo()` and `Instant::isNotEqualTo()` methods.
- `LocalDateTime::isEqualTo()` and `LocalDateTime::isNotEqualTo()` methods.
- `ZonedDateTime::isEqualTo()` and `ZonedDateTime::isNotEqualTo()` methods.

### Removed
- `DateTime::isEqualTo()` and `DateTime::isNotEqualTo()` methods.

## [0.2.3](https://github.com/frostealth/kronika/releases/tag/0.2.3) – 2025-11-11
### Added
- `Date::toPreviousMonth()` and `Date::toNextMonth()`.
- `Date::toPreviousWeek()` and `Date::toNextWeek()`.
- `Date::toYesterday()` and `Date::toTomorrow()`.
- `DayOfWeek::isWeekday()` and `DayOfWeek::isWeekend()`.
- `Hour::previous()` and `Hour::next()`.
- `Duration::ofWeek()` and `Duration::ofDay()`.
- `Duration::ofHour()`.
- `Range\DateRange`.
- `Range\DateTimeRange`.
- `Range\TimeRage`.

### Changed
- `Date\Month::previous()` doesn't return December for January anymore by default.
- `Date\DayOfWeek::next()` doesn't return Monday for Sunday anymore by default.

## [0.2.2](https://github.com/frostealth/kronika/releases/tag/0.2.2) – 2025-11-10
### Added
- Improvements in working with references.

### Fixed
- `*::instant()` and `*::ofInstant()` methods.
- Native format "U"/"U.u" for `LocalDateTime`.
- `LocalDateTime::toNative()`.

## [0.2.1](https://github.com/frostealth/kronika/releases/tag/0.2.1) – 2025-11-07
### Added
- `Date\DayOfYear`.
- `Date::dayOfYear()` and `DateTime::dayOfYear()`.
- `Date::toStartOfYear()` and `Date::isStartOfYear()`.
- `Date::toEndOfYear()` and `Date::isEndOfYear()`.
- `Date\DayOfMonth::first()`.
- `rolling` option to `*::with()` methods.

### Changed
- `rolling` option to `Date\Month::next()` method.
- `rolling` option to `Date\Month::previous()` method.
- `rolling` option to `Date\DayOfWeek::next()` method.
- `rolling` option to `Date\DayOfWeek::previous()` method.
- `Date\Month::next()` doesn't return January for December anymore by default.
- `Date\DayOfWeek::previous()` doesn't return Sunday for Monday anymore by default.

### Fixed
- Comparison via operators (`<`, `>`, `<=>`).

## [0.2.0](https://github.com/frostealth/kronika/releases/tag/0.2.0) – 2025-11-05
### Added
- Clock implementations.
- `earliest()` function.
- `latest()` function.
- `chronologize()` function.
- `timezone_system()` function.
- `timezone_utc()` function.
- `Date::parse()` method.
- `Time::parse()` method.
- Formatter.

### Removed
- Support for PHP v8.3.
- `*::is()` methods.
- `ZonedDateTime::shiftTimezone()` method.
- `LocalDateTime::modify()` method.
- `*::diff()` methods.
- `Instant::atTimezone()` method.
- `Duration::between()` method.

[unreleased]: https://github.com/frostealth/kronika/tree/0.x
