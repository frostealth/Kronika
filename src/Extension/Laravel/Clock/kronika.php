<?php

declare(strict_types=1);

return [
    /*
    | ––––––––––––––––––––––––––––––––––––––––––––––––––––––––––––––––––––
    | Default Clock
    | ––––––––––––––––––––––––––––––––––––––––––––––––––––––––––––––––––––
    |
    | Supported: "system", "inaccurate", "frozen", "mutable".
    */
    'clock' => \env('KRONIKA_CLOCK') ?: 'system',

    /*
    | ––––––––––––––––––––––––––––––––––––––––––––––––––––––––––––––––––––
    | "PSR-20: Clock"
    | ––––––––––––––––––––––––––––––––––––––––––––––––––––––––––––––––––––
    |
    | Register Kronika Clock as "PSR-20 ClockInterface".
    */
    'psr20' => false,

    /*
    | ––––––––––––––––––––––––––––––––––––––––––––––––––––––––––––––––––––
    | Clock Configurations
    | ––––––––––––––––––––––––––––––––––––––––––––––––––––––––––––––––––––
    */
    'clocks' => [
        'system' => [
            'timezone' => \env('KRONIKA_CLOCK_TIMEZONE') ?: null,
        ],
        'inaccurate' => [
            'clock' => \env('KRONIKA_CLOCK_INACCURATE_ORIGIN') ?: 'system',
            // supported precisions: "second", "minute".
            'precision' => \env('KRONIKA_CLOCK_INACCURATE_PRECISION') ?: 'second',
        ],
        'frozen' => [
            // a date-time string: '2025-12-31 12:30:45 +01:00'
            'time' => \env('KRONIKA_CLOCK_FROZEN_TIME') ?: null,
        ],
        'mutable' => [
            'clock' => \env('KRONIKA_CLOCK_MUTABLE_ORIGIN') ?: 'system',
        ],
    ],
];
