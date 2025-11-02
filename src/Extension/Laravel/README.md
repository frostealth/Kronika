# Laravel Extension
This extension allows you to automatically store Kronika objects
in a database with Laravel Eloquent.

## Usage
```php
<?php

use Illuminate\Database\Eloquent\Model;
use Kronika\Date;
use Kronika\Extension\Laravel\Eloquent\Casts\AsDate;
use Kronika\Extension\Laravel\Eloquent\Casts\AsTime;
use Kronika\Extension\Laravel\Eloquent\Casts\AsZonedDateTime;
use Kronika\Time;
use Kronika\ZonedDateTime;

/**
 * @property Time          $time
 * @property null|Date     $date
 * @property ZonedDateTime $datetime
 */
final class Foo extends Model
{
    #[\Override]
    protected function casts(): array
    {
        return [
            'time' => AsTime::class,
            'date' => AsDate::class,
            'datetime' => AsZonedDateTime::class,
        ];
    }
}

$foo = new Foo();
$foo->time = Time::of(21, 30, 45);
$foo->date = Date::of(2025, 12, 31);
$foo->datetime = ZonedDateTime::parse('2025-12-31 12:30:45.999999 +01:00');
$foo->save();

$foo = Foo::find(1);
echo $foo->time instanceof Time;               // true
echo $foo->date instanceof Date;               // true
echo $foo->datetime instanceof ZonedDateTime;  // true
```

See more in [Casts](Eloquent/Casts).
