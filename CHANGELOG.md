# Changelog
All notable changes to `Kronika` will be documented in this file.

## [Unreleased]
### Added

### Changed

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
- `Date\Month::previous()` doesn't return December for January anymore by default.
- `Date\DayOfWeek::next()` doesn't return Monday for Sunday anymore by default.
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
