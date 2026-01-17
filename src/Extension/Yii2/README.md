# Yii2 Extension
This extension allows you to automatically store Kronika objects
in a database with Yii2 ActiveRecord.

## Installation
```php
<?php

use Kronika\Time;
use Kronika\ZonedDateTime;
use Kronika\Extension\Yii2\ActiveRecord\KronikaBehavior;
use yii\db\ActiveRecord;

/**
 * @property Time          $opening
 * @property Time          $closing
 * @property ZonedDateTime $createdAt
 * @property ZonedDateTime $updatedAt 
 */
final class Foo extends ActiveRecord
{
    #[\Override]
    public function behaviors(): array
    {
        return [
            'kronika' => [
                'class' => KronikaBehavior::class,
                'formats' => [
                    // class-string => date/time format string
                    Time::class     => 'H:i:s.u',
                ],
                'attributes' => [
                    // class-string      => attribute names
                    Time::class          => ['opening', 'closing'],
                    ZonedDateTime::class => ['createdAt', 'updatedAt'],
                ],
            ],
        ];
    }
}
```
Database column type for:
- `Date`            -> string
- `Date\Year`       -> integer (tiny int)
- `Date\Month`      -> integer (unsigned tiny int)
- `Date\DayOfMonth` -> integer (unsigned tiny int)
- `Date\DayOfWeek`  -> integer (unsigned tiny int)
- `Date\DayOfYear`  -> integer (unsigned tiny int)
- `Time`            -> string
- `Time\Hour`       -> integer (unsigned tiny int)
- `Time\Minute`     -> integer (unsigned tiny int)
- `Time\Second`     -> float (unsigned tiny float)
- `Duration`        -> string | integer (unsigned int)
- `Instant`         -> float
- `LocalDateTime`   -> string or datetime without time-zone
- `ZonedDateTime`   -> string or datetime with time-zone
- `\DateTimeZone`   -> string

## Usage
```php
<?php

$foo = new Foo();
$foo->opening   = Time::of(hour: 12, minute: 30);
$foo->closing   = Time::of(hour: 20, minute: 30);
$foo->createdAt = ZonedDateTime::parse('2025-12-31 12:15:30.000999 UTC');
$foo->updatedAt = ZonedDateTime::parse('2025-12-31 12:15:30.999999 UTC');
$foo->save();

$foo = Foo::findOne($id);
echo \get_debug_type($foo->opening);   // Kronika\Time
echo \get_debug_type($foo->closing);   // Kronika\Time
echo \get_debug_type($foo->createdAt); // Kronika\ZonedDateTime
echo \get_debug_type($foo->updatedAt); // Kronika\ZonedDateTime
```
