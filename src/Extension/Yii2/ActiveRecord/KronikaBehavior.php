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

use DateTimeInterface as Native;
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
 * ```
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
 *                     \DateTimeZone::class => ['timezone'],
 *                 ],
 *                 'timezone' => [
 *                     'force' => fn():\DateTimeZone => $this->timezone,
 *                 ],
 *             ],
 *         ];
 *     }
 * }
 * ```
 *
 * @example
 * ```
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
 * - \DateTimeZone   -> string
 *
 * @psalm-type TAttributeName=non-empty-string
 * @psalm-type TFormattable=Date|Time|LocalDateTime|ZonedDateTime
 * @psalm-type TFormatOptions=array<class-string<TFormattable>, non-empty-string>
 * @psalm-type TForcedTimezone=null|\DateTimeZone|callable(): ?\DateTimeZone
 * @psalm-type TTimezoneOptions=array{store?: bool, suffix?: non-empty-string, force?: TForcedTimezone}
 *
 * @property BaseActiveRecord $owner
 */
final class KronikaBehavior extends Behavior
{
    /** @var TFormatOptions */
    public static array $defaultFormats = [
        Date::class => 'Y-m-d',
        Time::class => 'H:i:s.u',
        LocalDateTime::class => 'Y-m-d\TH:i:s.u',
        ZonedDateTime::class => 'Y-m-d\TH:i:s.uP',
    ];

    /** @var TTimezoneOptions */
    public static array $defaultTimezone = [
        'store' => false,
        'suffix' => '_timezone',
        'force' => null,
    ];

    /**
     * ```
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
     * ```
     * 'formats' => [
     *     Time::class => 'H:i:s.u',
     * ],
     * ```
     *
     * @var TFormatOptions|[]
     */
    public array $formats = [];

    /**
     * ```
     * // storing time-zones in an additional attributes
     * // with the 'Timezone' suffix ('createdAtTimezone').
     * 'attributes' => [
     *     ZonedDateTime::class => ['createdAt', 'updateAt'],
     * ],
     * 'timezone' => [
     *     'store' => true,  // default false
     *     'suffix' => 'Timezone',
     * ],
     *
     * $foo = new Foo();
     * $foo->createdAt = now(new \DateTimeZone("UTC"));
     * $foo->updatedAt = now(new \DateTimeZone("+02:00"));
     * $foo->save(); $foo->refresh();
     *
     * $foo->createdAt->format("Y-m-d H:i:s P"); // 2025-12-31 12:15:45 +00:00
     * $foo->updatedAt->format("Y-m-d H:i:s P"); // 2025-12-31 14:15:45 +02:00
     * $foo->createdAtTimezone->getName();       // UTC
     * $foo->updatedAtTimezone->getName();       // +02:00
     *
     * // forcing a time-zone.
     * // all ZonedDateTime will be shifted to a given time-zone.
     * 'attributes' => [
     *     ZonedDateTime::class => ['createdAt', 'updatedAt'],
     *     \DateTimeZone::class => ['timezone'],
     * ],
     * 'timezone' => [
     *     'force' => fn():\DateTimeZone => $this->timezone,  // default null
     * ],
     *
     * $foo = new Foo();
     * $foo->createdAt = now(new \DateTimeZone("UTC"));
     * $foo->updatedAt = now(new \DateTimeZone("+02:00"));
     * $foo->timezone = new \DateTimeZone("+01:00");
     * $foo->save(); $foo->refresh();
     *
     * $foo->createdAt->format("Y-m-d H:i:s P"); // 2025-12-31 13:15:45 +01:00
     * $foo->updatedAt->format("Y-m-d H:i:s P"); // 2025-12-31 13:15:45 +01:00
     * ```
     *
     * @var TTimezoneOptions
     */
    public array $timezone = [];

    /**
     * ```
     * 'attributes' => [
     *     ZonedDateTime::class => ['createdAt'],
     * ],
     * 'timezone' => [
     *     'force' => \DateTimeZone('UTC'),
     * ],
     *
     * $foo = new Foo();
     * $foo->createdAt = now(new \DateTimeZone("+01:00"));
     * $foo->createdAt->format("Y-m-d\TH:i:sP"); // 2025-12-31T13:15:45+01:00
     *
     * $foo->syncTimezones();
     * $foo->createdAt->format("Y-m-d\TH:i:sP"); // 2025-12-31T12:15:45+00:00
     * ```
     */
    public function syncTimezones(): void
    {
        $this->forceTimezone();
        $this->storeTimezones();
    }

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
            BaseActiveRecord::EVENT_AFTER_FIND => $this->castAllToObjects(...),
            BaseActiveRecord::EVENT_AFTER_INSERT => $this->castAllToObjects(...),
            BaseActiveRecord::EVENT_AFTER_UPDATE => $this->castAllToObjects(...),

            BaseActiveRecord::EVENT_BEFORE_INSERT => $this->castAllToDatabaseValues(...),
            BaseActiveRecord::EVENT_BEFORE_UPDATE => $this->castAllToDatabaseValues(...),
        ];
    }

    private function assertAttributes(BaseActiveRecord $owner): void
    {
        static $supportedTypes = [
            Date::class, Date\Year::class, Date\Month::class, Date\DayOfMonth::class, Date\DayOfWeek::class,
            Time::class, Time\Hour::class, Time\Minute::class, Time\Second::class,
            Duration::class, Instant::class, LocalDateTime::class, ZonedDateTime::class,
            \DateTimeZone::class,
        ];

        foreach ($this->attributes as $type => $names) {
            \assert(\in_array($type, $supportedTypes, true), "Unknown type: [$type]");
            foreach ($names as $name) {
                \assert($owner->hasAttribute($name), \sprintf(
                    "Attribute [%s::%s] doesn't exist",
                    $owner::class,
                    $name,
                ));
            }
        }
    }

    private function castAllToDatabaseValues(): void
    {
        $this->syncTimezones();

        $this->castAttributesToDatabaseValues($this->attributeNames());
        $this->castAttributesToDatabaseValues($this->timezoneAttributeNames());
    }

    private function castAllToObjects(): void
    {
        $this->castAttributesToObjects($this->timezoneAttributeNames());
        $this->castAttributesToObjects($this->attributeNames());
    }

    /** @param iterable<TAttributeName> $attributes */
    private function castAttributesToDatabaseValues(iterable $attributes): void
    {
        foreach ($attributes as $attributeName) {
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

    /** @param iterable<TAttributeName> $attributes */
    private function castAttributesToObjects(iterable $attributes): void
    {
        foreach ($attributes as $attributeName) {
            $attribute = $this->owner->getAttribute($attributeName);
            if (null !== $attribute) {
                $this->owner->setAttribute($attributeName, $this->toObject($attributeName, $attribute));
            }

            $oldAttribute = $this->owner->getOldAttribute($attributeName);
            if (null !== $oldAttribute && $this->owner->canSetOldAttribute($attributeName)) {
                $this->owner->setOldAttribute($attributeName, $this->toObject($attributeName, $oldAttribute));
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
            $obj instanceof Native => $obj->format('Y-m-d H:i:s.u P'),
            $obj instanceof \DateTimeZone => $obj->getName(),
            default => throw new \RuntimeException(\sprintf('Unknown type: [%s]', \get_debug_type($obj))),
        };
    }

    /**
     * @param TAttributeName $attributeName
     * @param numeric|non-empty-string $value
     */
    private function toObject(string $attributeName, float|int|string $value): object
    {
        return match ($this->getTypeOf($attributeName)) {
            Date::class            => Date::tryOfFormat(
                format: $this->getFormatFor(Date::class),
                date: (string)$value,
            ) ?? Date::parse((string)$value),
            Date\Year::class       => Date\Year::of((int)$value),
            Date\Month::class      => Date\Month::of((int)$value),
            Date\DayOfMonth::class => Date\DayOfMonth::of((int)$value),
            Date\DayOfWeek::class  => Date\DayOfWeek::of((int)$value),
            Time::class            => Time::tryOfFormat(
                format: $this->getFormatFor(Time::class),
                time: (string)$value,
            ) ?? Time::parse((string)$value),
            Time\Hour::class       => Time\Hour::of((int)$value),
            Time\Minute::class     => Time\Minute::of((int)$value),
            Time\Second::class     => Time\Second::of(\sscanf((string)$value, '%d.%6d')),
            Duration::class        => Duration::of(seconds: (int)$value),
            Instant::class         => Instant::ofValue($value),
            LocalDateTime::class   => LocalDateTime::tryOfFormat(
                format: $this->getFormatFor(LocalDateTime::class),
                datetime: (string)$value,
            ) ?? LocalDateTime::parse((string)$value),
            ZonedDateTime::class   => $this->adjustTimezone($attributeName, ZonedDateTime::tryOfFormat(
                format: $this->getFormatFor(ZonedDateTime::class),
                datetime: (string)$value,
            ) ?? ZonedDateTime::parse((string)$value)),
            \DateTimeZone::class   => new \DateTimeZone((string)$value),
        };
    }

    private function forceTimezone(): void
    {
        $timezone = $this->forcedTimezone();
        if (! $timezone instanceof \DateTimeZone) {
            return;
        }

        foreach ($this->zonedAttributeNames() as $name) {
            $datetime = $this->owner->getAttribute($name);
            $oldDatetime = $this->owner->getOldAttribute($name);
            if ($datetime instanceof Native) {
                $datetime = ZonedDateTime::ofDateTime($datetime);
            }
            if (! $datetime instanceof ZonedDateTime) {
                continue;
            }

            $this->owner->setAttribute($name, $datetime->shift($timezone));
            if ($this->owner->canSetOldAttribute($name)) {
                $this->owner->setOldAttribute($name, $oldDatetime);
            }
        }
    }

    private function storeTimezones(): void
    {
        if (! $this->shouldStoreTimezone()) {
            return;
        }

        foreach ($this->zonedAttributeNames() as $name) {
            $datetime = $this->owner->getAttribute($name);
            $datetime = $datetime instanceof Native ? ZonedDateTime::ofDateTime($datetime) : $datetime;
            $timezone = $datetime instanceof ZonedDateTime ? $datetime->timezone() : null;

            $this->owner->setAttribute($this->timezoneAttributeNameFor($name), $timezone);
        }
    }

    /** @param TAttributeName $name */
    private function adjustTimezone(string $name, ZonedDateTime $datetime): ZonedDateTime
    {
        $timezone = $this->forcedTimezone() ?? $this->owner->getAttribute($this->timezoneAttributeNameFor($name));
        if (! $timezone instanceof \DateTimeZone) {
            $timezone = $datetime->timezone();
        }

        return $datetime->shift($timezone);
    }

    /**
     * @param class-string<TFormattable> $type
     *
     * @return non-empty-string
     */
    private function getFormatFor(string $type): string
    {
        return $this->formats[$type] ?? self::$defaultFormats[$type];
    }

    /**
     * @param TAttributeName $attributeName
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

        if (\str_ends_with($attributeName, $this->timezoneSuffix())) {
            return \DateTimeZone::class;
        }

        throw new \RuntimeException("Unknown attribute: [$attributeName]");
    }

    /** @return iterable<TAttributeName> */
    private function attributeNames(): iterable
    {
        $attributes = $this->attributes;
        unset($attributes[\DateTimeZone::class]);

        foreach ($attributes as $names) {
            yield from $names;
        }
    }

    /** @return iterable<TAttributeName> */
    private function timezoneAttributeNames(): iterable
    {
        yield from $this->attributes[\DateTimeZone::class] ?? [];

        if ($this->shouldStoreTimezone()) {
            foreach ($this->zonedAttributeNames() as $attributeName) {
                yield $this->timezoneAttributeNameFor($attributeName);
            }
        }
    }

    /** @return iterable<TAttributeName> */
    private function zonedAttributeNames(): iterable
    {
        yield from $this->attributes[ZonedDateTime::class] ?? [];
    }

    /**
     * @param TAttributeName $attributeName
     *
     * @return TAttributeName
     */
    private function timezoneAttributeNameFor(string $attributeName): string
    {
        return $attributeName . $this->timezoneSuffix();
    }

    /** @return non-empty-string */
    private function timezoneSuffix(): string
    {
        return $this->timezone['suffix'] ?? self::$defaultTimezone['suffix'] ?? '_timezone';
    }

    private function forcedTimezone(): ?\DateTimeZone
    {
        $timezone = $this->timezone['force'] ?? self::$defaultTimezone['force'] ?? null;

        return \is_callable($timezone) ? $timezone() : $timezone;
    }

    private function shouldStoreTimezone(): bool
    {
        return $this->timezone['store'] ?? self::$defaultTimezone['store'] ?? false;
    }
}
