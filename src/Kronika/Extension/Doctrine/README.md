# Kronika support for Doctrine
This extension allows you to store Kronika objects in a database
with [Doctrine](https://www.doctrine-project.org/).

## Installation
```php
<?php
// in bootstrapping code

use Doctrine\DBAL\Types\Type;
use Kronika\Extension\Doctrine\Types as Kronika;

// Register the types
Type::addType(Kronika\DateType::NAME, Kronika\DateType::class);
Type::addType(Kronika\Date\YearType::NAME, Kronika\Date\YearType::class);
Type::addType(Kronika\Date\MonthType::NAME, Kronika\Date\MonthType::class);
Type::addType(Kronika\Date\DayOfWeekType::NAME, Kronika\Date\DayOfWeekType::class);
Type::addType(Kronika\Date\DayOfMonthType::NAME, Kronika\Date\DayOfMonthType::class);
Type::addType(Kronika\TimeType::NAME, Kronika\TimeType::class);
Type::addType(Kronika\Time\HourType::NAME, Kronika\Time\HourType::class);
Type::addType(Kronika\Time\MinuteType::NAME, Kronika\Time\MinuteType::class);
Type::addType(Kronika\Time\SecondType::NAME, Kronika\Time\SecondType::class);
Type::addType(Kronika\DurationType::NAME, Kronika\DurationType::class);
Type::addType(Kronika\InstantType::NAME, Kronika\InstantType::class);
Type::addType(Kronika\LocalDateTimeType::NAME, Kronika\LocalDateTimeType::class);
Type::addType(Kronika\ZonedDateTimeType::NAME, Kronika\ZonedDateTimeType::class);
```
Or 
```yaml
# config/packages/doctrine.yaml
doctrine:
  dbal:
    types:
      kronika.date: Kronika\Extension\Doctrine\Types\DateType
      kronika.year: Kronika\Extension\Doctrine\Types\Date\YearType
      kronika.month: Kronika\Extension\Doctrine\Types\Date\MonthType
      kronika.day-of-week: Kronika\Extension\Doctrine\Types\Date\DayOfWeekType
      kronika.day-of-month: Kronika\Extension\Doctrine\Types\Date\DayOfMonthType
      kronika.time: Kronika\Extension\Doctrine\Types\TimeType
      kronika.hour: Kronika\Extension\Doctrine\Types\Time\HourType
      kronika.minute: Kronika\Extension\Doctrine\Types\Time\MinuteType
      kronika.second: Kronika\Extension\Doctrine\Types\Time\SecondType
      kronika.duration: Kronika\Extension\Doctrine\Types\DurationType
      kronika.instant: Kronika\Extension\Doctrine\Types\InstantType
      kronika.local-datetime: Kronika\Extension\Doctrine\Types\LocalDateTimeType
      kronika.zoned-datetime: Kronika\Extension\Doctrine\Types\ZonedDateTimeType
```

## Usage
```php
<?php

use Doctrine\ORM\Mapping\Column;
use Kronika\Extension\Doctrine\Types\TimeType;
use Kronika\Extension\Doctrine\Types\ZonedDateTimeType;
use Kronika\Time;
use Kronika\ZonedDateTime;

final class Foo
{
    #[Column(type: TimeType::NAME)]
    private Time $time;

    #[Column(type: ZonedDateTimeType::NAME)]
    private ZonedDateTime $createdAt;
}
```
