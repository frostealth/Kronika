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

namespace Kronika\Extension\JmsSerializer\Handlers;

use JMS\Serializer\Visitor\DeserializationVisitorInterface as DeserializationVisitor;
use JMS\Serializer\Visitor\SerializationVisitorInterface as SerializationVisitor;
use Kronika\DateTime;
use Kronika\Unit;

/**
 * @method serialize(SerializationVisitor $visitor)
 * @method deserialize(DeserializationVisitor $visitor)
 */
interface Handler
{
    /** @return non-empty-list<class-string<DateTime|Unit>|non-empty-string> */
    public function types(): array;
}
