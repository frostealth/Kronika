<?php

declare(strict_types=1);

/**
 * This file is part of the Kronika package.
 *
 * (c) Ivan Kudinov <i@ikudinov.pro>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Kronika\Extension\Doctrine;

use Doctrine\DBAL\Types\Type as DoctrineType;

final readonly class Types
{
    public static function register(): void
    {
        DoctrineType::addType(Types\DateType::NAME, Types\DateType::class);
        DoctrineType::addType(Types\Date\YearType::NAME, Types\Date\YearType::class);
        DoctrineType::addType(Types\Date\MonthType::NAME, Types\Date\MonthType::class);
        DoctrineType::addType(Types\Date\DayOfMonthType::NAME, Types\Date\DayOfMonthType::class);
        DoctrineType::addType(Types\Date\DayOfWeekType::NAME, Types\Date\DayOfWeekType::class);

        DoctrineType::addType(Types\TimeType::NAME, Types\TimeType::class);
        DoctrineType::addType(Types\Time\HourType::NAME, Types\Time\HourType::class);
        DoctrineType::addType(Types\Time\MinuteType::NAME, Types\Time\MinuteType::class);
        DoctrineType::addType(Types\Time\SecondType::NAME, Types\Time\SecondType::class);

        DoctrineType::addType(Types\DurationType::NAME, Types\DurationType::class);
        DoctrineType::addType(Types\InstantType::NAME, Types\InstantType::class);
        DoctrineType::addType(Types\LocalDateTimeType::NAME, Types\LocalDateTimeType::class);
        DoctrineType::addType(Types\ZonedDateTimeType::NAME, Types\ZonedDateTimeType::class);
    }

    private function __construct() {
    }
}
