<?php

namespace Illuminate\Foundation\Testing\Concerns;

use DateTimeInterface;
use Illuminate\Foundation\Testing\Wormhole;
use Illuminate\Support\Carbon;

/**
 * Trait InteractsWithTime
 * @package Illuminate\Foundation\Testing\Concerns
 *
 * Provided as a polyfill from Laravel 8.x to Laravel 6.x and 7.x for testing.
 * This is required by tests/Orchestra/Testbench/TestCase.php.
 *
 * When Laravel 8.x is installed, the file from Laravel 8.x is prioritized over this file.
 */
trait InteractsWithTime
{
    /**
     * Begin travelling to another time.
     *
     * @param  int  $value
     * @return \Illuminate\Foundation\Testing\Wormhole
     */
    public function travel($value)
    {
        return new Wormhole($value);
    }

    /**
     * Travel to another time.
     *
     * @param  \DateTimeInterface  $date
     * @param  callable|null  $callback
     * @return mixed
     */
    public function travelTo(DateTimeInterface $date, $callback = null)
    {
        Carbon::setTestNow($date);

        if ($callback) {
            return tap($callback(), function () {
                Carbon::setTestNow();
            });
        }
    }

    /**
     * Travel back to the current time.
     *
     * @return \DateTimeInterface
     */
    public function travelBack()
    {
        return Wormhole::back();
    }
}
