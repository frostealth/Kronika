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

namespace Kronika\Extension\JmsSerializer;

use JMS\Serializer\Context;
use JMS\Serializer\GraphNavigatorInterface as GraphNavigator;
use JMS\Serializer\Handler\SubscribingHandlerInterface;
use JMS\Serializer\Visitor\DeserializationVisitorInterface as DeserializationVisitor;
use JMS\Serializer\Visitor\SerializationVisitorInterface as SerializationVisitor;
use Kronika\Extension\JmsSerializer\Handlers\DateHandler;
use Kronika\Extension\JmsSerializer\Handlers\DayOfMonthHandler;
use Kronika\Extension\JmsSerializer\Handlers\DayOfWeekHandler;
use Kronika\Extension\JmsSerializer\Handlers\DurationHandler;
use Kronika\Extension\JmsSerializer\Handlers\Handler;
use Kronika\Extension\JmsSerializer\Handlers\HourHandler;
use Kronika\Extension\JmsSerializer\Handlers\InstantHandler;
use Kronika\Extension\JmsSerializer\Handlers\LocalDateTimeHandler;
use Kronika\Extension\JmsSerializer\Handlers\MinuteHandler;
use Kronika\Extension\JmsSerializer\Handlers\MonthHandler;
use Kronika\Extension\JmsSerializer\Handlers\SecondHandler;
use Kronika\Extension\JmsSerializer\Handlers\TimeHandler;
use Kronika\Extension\JmsSerializer\Handlers\YearHandler;
use Kronika\Extension\JmsSerializer\Handlers\ZonedDateTimeHandler;

final class KronikaSubscribingHandler implements SubscribingHandlerInterface
{
    /** @var null|array<non-empty-string, Handler> */
    private static ?array $_handlers = null;

    #[\Override]
    public static function getSubscribingMethods(): array
    {
        $methods = [];
        $formats = ['json', 'xml', 'yml'];

        foreach (self::handlers() as $type => $handler) {
            foreach ($formats as $format) {
                $methods[] = [
                    'type' => $type,
                    'method' => 'serialize',
                    'direction' => GraphNavigator::DIRECTION_SERIALIZATION,
                    'format' => $format,
                ];
                $methods[] = [
                    'type' => $type,
                    'method' => 'deserialize',
                    'direction' => GraphNavigator::DIRECTION_DESERIALIZATION,
                    'format' => $format,
                ];
            }
        }

        return $methods;
    }

    /** @return array<non-empty-string, Handler> */
    private static function handlers(): array
    {
        if (self::$_handlers !== null) {
            return self::$_handlers;
        }

        $handlers = [
            new DateHandler(), new YearHandler(), new MonthHandler(), new DayOfMonthHandler(), new DayOfWeekHandler(),
            new TimeHandler(), new HourHandler(), new MinuteHandler(), new SecondHandler(),
            new DurationHandler(), new InstantHandler(),
            new LocalDateTimeHandler(), new ZonedDateTimeHandler(),
        ];

        foreach ($handlers as $handler) {
            foreach ($handler->types() as $type) {
                self::$_handlers[$type] = $handler;
            }
        }

        return self::$_handlers;
    }

    public function serialize(SerializationVisitor $visitor, mixed $value, array $type, Context $context): mixed
    {
        return self::handlers()[$type['name']]->serialize($visitor, $value, $type, $context);
    }

    public function deserialize(DeserializationVisitor $visitor, mixed $value, array $type, Context $context): mixed
    {
        return self::handlers()[$type['name']]->deserialize($visitor, $value, $type, $context);
    }
}
