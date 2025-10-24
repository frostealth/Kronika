# Kronika support for Yii2
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
- `Date\Year`       -> integer (unsigned tiny int)
- `Date\Month`      -> integer (unsigned tiny int)
- `Date\DayOfMonth` -> integer (unsigned tiny int)
- `Date\DayOfWeek`  -> integer (unsigned tiny int)
- `Time`            -> string
- `Time\Hour`       -> integer (unsigned tiny int)
- `Time\Minute`     -> integer (unsigned tiny int)
- `Time\Second`     -> float (unsigned tiny float)
- `Duration`        -> integer (unsigned int)
- `Instant`         -> float
- `LocalDateTime`   -> string (datetime without time-zone)
- `ZonedDateTime`   -> string (datetime with time-zone)

## Usage
```php
<?php

use function Kronika\now;

$foo = new Foo();
$foo->opening   = Time::of(hour: 12, minute: 30);
$foo->closing   = Time::of(hour: 20, minute: 30);
$foo->createdAt = now();
$foo->updatedAt = now();
$foo->save();

$foo = Foo::findOne($id);
echo \get_debug_type($foo->opening);   // Kronika\Time
echo \get_debug_type($foo->closing);   // Kronika\Time
echo \get_debug_type($foo->createdAt); // Kronika\ZonedDateTime
echo \get_debug_type($foo->updatedAt); // Kronika\ZonedDateTime
```
