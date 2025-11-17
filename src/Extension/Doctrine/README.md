# Doctrine Extension
This extension allows you to store Kronika objects in a database
with [Doctrine](https://www.doctrine-project.org/).

## Installation
```php
<?php
// in bootstrapping code

use Kronika\Extension\Doctrine\Types as KronikaTypes;

// Register the types
KronikaTypes::register();
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
      kronika.day-of-month: Kronika\Extension\Doctrine\Types\Date\DayOfMonthType
      kronika.day-of-week: Kronika\Extension\Doctrine\Types\Date\DayOfWeekType
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
