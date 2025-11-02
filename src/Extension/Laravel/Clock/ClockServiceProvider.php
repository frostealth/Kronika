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

namespace Kronika\Extension\Laravel\Clock;

use Illuminate\Config\Repository as Config;
use Illuminate\Support\ServiceProvider;
use Kronika\Clock;
use Kronika\Precision;
use Kronika\ZonedDateTime;
use function Kronika\clock;

class ClockServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__ . '/kronika.php' => $this->app->configPath('kronika.php'),
            ], 'kronika');
        }

        $this->booted(function (): void {
            clock($this->app->get(Clock::class));
        });
    }

    #[\Override]
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__ . '/kronika.php', 'kronika');

        $this->app->singleton(Clock::class, function () {
            return $this->createClock($this->config()->get('kronika.clock', 'system'));
        });
        $this->app->alias(Clock::class, 'kronika.clock');

        if (
            $this->config()->get('kronika.psr20', false) === true
            && \interface_exists('Psr\\Clock\\ClockInterface')
        ) {
            $this->app->singleton(\Psr\Clock\ClockInterface::class, function (): \Psr\Clock\ClockInterface {
                return new Clock\PsrClock($this->app->get(Clock::class));
            });
        }
    }

    protected function createClock(string $name): Clock
    {
        return match($name) {
            'system' => $this->createSystemClock(),
            'inaccurate' => $this->createInaccurateClock(),
            'frozen' => $this->createFrozenClock(),
            'mutable' => $this->createMutableClock(),
        };
    }

    private function createSystemClock(): Clock\SystemClock
    {
        return new Clock\SystemClock(timezone: $this->getSystemClockTimezone());
    }

    private function createInaccurateClock(): Clock\InaccurateClock
    {
        $clock = $this->config()->get('kronika.clocks.inaccurate.clock', 'system');
        $precision = match($this->config()->get('kronika.clocks.inaccurate.precision', 'second')) {
            'minute' => Precision::Minute,
            'second' => Precision::Second,
            default => throw new \RuntimeException('Unknown or unsupported inaccurate clock precision'),
        };

        return new Clock\InaccurateClock(clock: $this->createClock($clock), precision: $precision);
    }

    private function createFrozenClock(): Clock\FrozenClock
    {
        $time = $this->config()->get('kronika.clocks.frozen.time');
        if (\is_string($time)) {
            $time = ZonedDateTime::parse($time);
        }

        return new Clock\FrozenClock(time: $time);
    }

    private function createMutableClock(): Clock\MutableClock
    {
        $clock = $this->config()->get('kronika.clocks.mutable.clock', 'frozen');

        return new Clock\MutableClock(clock: $this->createClock($clock));
    }

    private function config(): Config
    {
        return $this->app->get(Config::class);
    }

    /** @return null|\DateTimeZone|(callable(): ?\DateTimeZone) */
    protected function getSystemClockTimezone(): null|\DateTimeZone|callable
    {
        $timezone = $this->config()->get(
            key: 'kronika.clocks.system.timezone',
            default: $this->config()->get('app.timezone'),
        );
        if ($timezone === '') {
            return null;
        }
        if (\is_string($timezone)) {
            $timezone = new \DateTimeZone($timezone);
        }

        return $timezone;
    }
}
