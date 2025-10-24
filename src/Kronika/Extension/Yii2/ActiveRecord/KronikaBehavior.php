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

namespace Kronika\Extension\Yii2\ActiveRecord;

use Kronika\Date;
use Kronika\Duration;
use Kronika\Instant;
use Kronika\LocalDateTime;
use Kronika\Time;
use Kronika\ZonedDateTime;
use yii\base\Behavior;
use yii\db\BaseActiveRecord;

/**
 * KronikaBehavior automatically serializes/deserializes Kronika objects into/from Database types.
 *
 * ```php
 * use Kronika\Time;
 * use Kronika\ZonedDateTime;
 * use Kronika\Extension\Yii2\ActiveRecord\KronikaBehavior;
 * use yii\db\ActiveRecord;
 *
 * final class Foo extends ActiveRecord
 * {
 *     #[\Override]
 *     public function behaviors(): array
 *     {
 *         return [
 *             'kronika' => [
 *                 'class' => KronikaBehavior::class,
 *                 'formats' => [
 *                     // class-string => date/time format string
 *                     Time::class     => 'H:i:s.u',
 *                 ],
 *                 'attributes' => [
 *                     // class-string      => attribute names
 *                     Time::class          => ['opening', 'closing'],
 *                     ZonedDateTime::class => ['createdAt', 'updatedAt'],
 *                 ],
 *             ],
 *         ];
 *     }
 * }
 *
 * use function Kronika\now;
 *
 * $foo = new Foo();
 * $foo->opening   = Time::of(hour: 12, minute: 30);
 * $foo->closing   = Time::of(hour: 20, minute: 30);
 * $foo->createdAt = now();
 * $foo->updatedAt = now();
 * $foo->save();
 *
 * $foo = Foo::findOne($foo->id);
 * echo \get_debug_type($foo->opening);   // Kronika\Time
 * echo \get_debug_type($foo->closing);   // Kronika\Time
 * echo \get_debug_type($foo->createdAt); // Kronika\ZonedDateTime
 * echo \get_debug_type($foo->updatedAt); // Kronika\ZonedDateTime
 * ```
 *
 * Database column type for:
 * - Date            -> string
 * - Date\Year       -> integer (unsigned tiny int)
 * - Date\Month      -> integer (unsigned tiny int)
 * - Date\DayOfMonth -> integer (unsigned tiny int)
 * - Date\DayOfWeek  -> integer (unsigned tiny int)
 * - Time            -> string
 * - Time\Hour       -> integer (unsigned tiny int)
 * - Time\Minute     -> integer (unsigned tiny int)
 * - Time\Second     -> float (unsigned tiny float)
 * - Duration        -> integer (unsigned int)
 * - Instant         -> float
 * - LocalDateTime   -> string (datetime without time-zone)
 * - ZonedDateTime   -> string (datetime with time-zone)
 *
 * @psalm-type TAttributeName=non-empty-string
 * @psalm-type TFormattable=Date|Time|LocalDateTime|ZonedDateTime
 *
 * @property BaseActiveRecord $owner
 */
final class KronikaBehavior extends Behavior
{
    /** @var array<class-string<TFormattable>, non-empty-string> */
    public static array $defaultFormats = [
        Date::class => 'Y-m-d',
        Time::class => 'H:i:s',
        LocalDateTime::class => 'Y-m-d\TH:i:s',
        ZonedDateTime::class => \DateTimeInterface::ATOM,
    ];

    /**
     * ```php
     * 'attributes' => [
     *     Date::class => ['registeredOn'],
     *     Time::class => ['opening', 'closing'],
     *     ZonedDateTime::class => ['createdAt', 'updatedAt'],
     * ],
     * ```
     *
     * @var array<class-string, list<TAttributeName>>
     */
    public array $attributes = [];

    /**
     * ```php
     * 'formats' => [
     *     Time::class => 'H:i:s.u',
     * ],
     * ```
     *
     * @var array<class-string<TFormattable>, non-empty-string>
     */
    public array $formats = [];

    #[\Override]
    public function attach($owner): void
    {
        \assert($owner instanceof BaseActiveRecord);
        $this->assertAttributes($owner);

        parent::attach($owner);
    }

    #[\Override]
    public function events(): array
    {
        return [
            BaseActiveRecord::EVENT_AFTER_FIND => $this->castAttributesToObjects(...),
            BaseActiveRecord::EVENT_AFTER_INSERT => $this->castAttributesToObjects(...),
            BaseActiveRecord::EVENT_AFTER_UPDATE => $this->castAttributesToObjects(...),

            BaseActiveRecord::EVENT_BEFORE_INSERT => $this->castAttributesToDatabaseValues(...),
            BaseActiveRecord::EVENT_BEFORE_UPDATE => $this->castAttributesToDatabaseValues(...),
        ];
    }

    private function assertAttributes(BaseActiveRecord $owner): void
    {
        static $kronikaTypes = [
            Date::class, Date\Year::class, Date\Month::class, Date\DayOfMonth::class, Date\DayOfWeek::class,
            Time::class, Time\Hour::class, Time\Minute::class, Time\Second::class,
            Duration::class, Instant::class, LocalDateTime::class, ZonedDateTime::class,
        ];

        foreach ($this->attributes as $type => $names) {
            \assert(\in_array($type, $kronikaTypes, true), "Unknown type: [$type]");
            foreach ($names as $name) {
                \assert($owner->hasAttribute($name), \sprintf(
                    "Attribute [%s::%s] doesn't exist",
                    $owner::class,
                    $name,
                ));
            }
        }
    }

    private function castAttributesToDatabaseValues(): void
    {
        foreach ($this->attributeNames() as $attributeName) {
            $attribute = $this->owner->getAttribute($attributeName);
            if (\is_object($attribute)) {
                $this->owner->setAttribute($attributeName, $this->toDatabaseValue($attribute));
            }

            $oldAttribute = $this->owner->getOldAttribute($attributeName);
            if (\is_object($oldAttribute) && $this->owner->canSetOldAttribute($attributeName)) {
                $this->owner->setOldAttribute($attributeName, $this->toDatabaseValue($oldAttribute));
            }
        }
    }

    private function castAttributesToObjects(): void
    {
        foreach ($this->attributeNames() as $attributeName) {
            $attribute = $this->owner->getAttribute($attributeName);
            if (null !== $attribute) {
                $this->owner->setAttribute($attributeName, $this->toObject($attributeName, $attribute));
            }

            $oldAttribute = $this->owner->getOldAttribute($attributeName);
            if (null !== $oldAttribute && $this->owner->canSetOldAttribute($attributeName)) {
                $this->owner->setOldAttribute($attributeName, $this->toObject($oldAttribute, $attribute));
            }
        }
    }

    private function toDatabaseValue(object $obj): float|int|string
    {
        return match (true) {
            $obj instanceof Date => $obj->format($this->getFormatFor(Date::class)),
            $obj instanceof Date\Year,
            $obj instanceof Date\Month,
            $obj instanceof Date\DayOfMonth,
            $obj instanceof Date\DayOfWeek => $obj->number(),
            $obj instanceof Time => $obj->format($this->getFormatFor(Time::class)),
            $obj instanceof Time\Hour,
            $obj instanceof Time\Minute,
            $obj instanceof Time\Second => $obj->value(),
            $obj instanceof Duration => $obj->inSeconds(),
            $obj instanceof Instant => $obj->value(),
            $obj instanceof LocalDateTime => $obj->format($this->getFormatFor(LocalDateTime::class)),
            $obj instanceof ZonedDateTime => $obj->format($this->getFormatFor(ZonedDateTime::class)),
            default => throw new \RuntimeException(\sprintf('Unknown type: [%s]', \get_debug_type($obj))),
        };
    }

    private function toObject(string $attributeName, float|int|string $value): object
    {
        return match ($this->getTypeOf($attributeName)) {
            Date::class            => Date::ofFormat(format: $this->getFormatFor(Date::class), date: (string) $value),
            Date\Year::class       => Date\Year::of((int) $value),
            Date\Month::class      => Date\Month::of((int) $value),
            Date\DayOfMonth::class => Date\DayOfMonth::of((int) $value),
            Date\DayOfWeek::class  => Date\DayOfWeek::of((int) $value),
            Time::class            => Time::ofFormat(format: $this->getFormatFor(Time::class), time: (string) $value),
            Time\Hour::class       => Time\Hour::of((int) $value),
            Time\Minute::class     => Time\Minute::of((int) $value),
            Time\Second::class     => Time\Second::of(...\sscanf((string) $value, '%d.%6d')),
            Duration::class        => Duration::of(seconds: (int) $value),
            Instant::class         => Instant::ofValue($value),
            LocalDateTime::class   => ZonedDateTime::ofFormat(
                format: $this->getFormatFor(ZonedDateTime::class),
                datetime: (string) $value,
            )->toLocalDateTime(),
            ZonedDateTime::class   => ZonedDateTime::ofFormat(
                format: $this->getFormatFor(ZonedDateTime::class),
                datetime: (string) $value,
            ),
        };
    }

    /**
     * @param class-string<Date|Time|LocalDateTime|ZonedDateTime> $type
     *
     * @return non-empty-string
     */
    private function getFormatFor(string $type): string
    {
        return $this->formats[$type] ?? self::$defaultFormats[$type];
    }

    /**
     * @param non-empty-string $attributeName
     *
     * @return class-string
     */
    private function getTypeOf(string $attributeName): string
    {
        foreach ($this->attributes as $type => $names) {
            if (\in_array($attributeName, $names, true)) {
                return $type;
            }
        }

        throw new \RuntimeException("Unknown attribute: [$attributeName]");
    }

    /** @return iterable<TAttributeName> */
    private function attributeNames(): iterable
    {
        foreach ($this->attributes as $names) {
            yield from $names;
        }
    }
}
